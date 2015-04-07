<?php
namespace Upyun;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Stream\Stream;
use Upyun\Exceptions\BadRequestException;
use Upyun\Exceptions\ForbiddenException;
use Upyun\Exceptions\NotAcceptableException;
use Upyun\Exceptions\NotFoundException;
use Upyun\Exceptions\ServiceUnavailableException;
use Upyun\Exceptions\UnauthorizationException;
use Upyun\Exceptions\UnknownException;
use Upyun\Util\Directory;
use Upyun\Util\FileInfo;

class Client
{
    /**
     * Version
     */
    const VERSION = '0.1.1';

    /**
     * @var string Bucket
     */
    protected $bucket;

    /**
     * @var string operator username
     */
    protected $username;

    /**
     * @var string operator password
     */
    protected $password;

    /**
     * @var bool debug mode
     */
    protected $debug = false;

    /**
     * @var string upyun server
     */
    protected $endpoint = 'http://v0.api.upyun.com';

    /**
     * Construct
     * @param array $options
     * @throws BadRequestException
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['bucket']) || !isset($options['username']) || !isset($options['password'])) {
            throw new BadRequestException(_('You must fill Username, Password and Bucket'));
        }

        $this->bucket = $options['bucket'];
        $this->username = $options['username'];
        $this->password = $options['password'];

        if (isset($options['endpoint'])) {
            $this->endpoint = $options['endpoint'];
        }
        if (isset($options['debug'])) {
            $this->debug = (bool)$options['debug'];
        }
    }

    /**
     * Get Client Version
     * @return string
     */
    public function version()
    {
        return self::VERSION;
    }

    /**
     * @param string $path Remote file path
     * @param string|resource $file File resource, File path or Raw content
     * @param array $options
     * @return bool
     * @throws
     */
    public function put($path, $file, array $options = [])
    {
        if (is_resource($file)) {
            $options = ['file' => $file];
        } elseif (is_string($file) && file_exists($file)) {
            $options = ['file' => fopen($file, 'r')];
        } elseif (is_string($file)) {
            $options = ['content' => $file];
        } else {
            throw new BadRequestException();
        }
        $response = $this->performRequest('PUT', $path, $options);
        return $this->returnOrThrow($response);
    }

    /**
     * @param string $path Remote file path
     * @return mixed
     */
    public function get($path)
    {
        $response = $this->performRequest('GET', $path);
        if ($response->getStatusCode() == 200) {
            return (string)$response->getBody();
        } else {
            return $this->returnOrThrow($response);
        }
    }

    /**
     * @param string $path Remote file path
     * @return \Upyun\Util\FileInfo
     */
    public function head($path)
    {
        $response = $this->performRequest('HEAD', $path);
        if ($response->getStatusCode() == 200) {
            return new FileInfo(FileInfo::parseHeader($response->getHeaders()), ['filename' => $path]);
        } else {
            return $this->returnOrThrow($response);
        }
    }

    /**
     * @param string $path Remote file path
     * @return bool
     */
    public function delete($path)
    {
        $response = $this->performRequest('DELETE', $path);
        return $this->returnOrThrow($response);
    }

    /**
     * @param string $path Remote file path
     * @return bool
     */
    public function mkdir($path)
    {
        $response = $this->performRequest('POST', $path, ['folder' => true]);
        return $this->returnOrThrow($response);
    }

    /**
     * @param string $path Remote file path
     * @param bool $recursive
     * @return \Upyun\Util\Directory
     * @throws
     */
    public function ls($path, $recursive = false)
    {
        $fileinfo = $this->head($path);
        if (!$fileinfo || $fileinfo->isFile()) {
            throw new BadRequestException('Invalid list directory on a file');
        }

        if ($recursive) {
            return $this->recursiveList($path);
        }

        $response = $this->performRequest('GET', $path);
        if ($response->getStatusCode() == 200) {
            return new Directory(Directory::parseBody((string)$response->getBody()));
        } else {
            return $this->returnOrThrow($response);
        }
    }

    /**
     * @param string $path Remote file path
     * @return bool
     */
    public function rmrf($path)
    {
        $fileinfo = $this->head($path);
        if ($fileinfo->getType() == 'file') {
            return $this->delete($path);
        } else {
            return $this->recursiveDelete($path);
        }
    }

    /**
     * @param $path
     * @return \Upyun\Util\Directory
     */
    protected function recursiveList($path)
    {
        $files = $this->recursiveRetrieve($path);
        return new Directory($files, true);
    }

    /**
     * @param string $path Dir path
     * @return array
     */
    protected function recursiveRetrieve($path)
    {
        $response = $this->performRequest('GET', $path);
        $files = Directory::parseBody((string)$response->getBody());
        foreach ($files as &$file) {
            if ($file['type'] == 'dir') {
                $file['files'] = $this->recursiveRetrieve($path . '/' . $file['filename']);
            }
        }
        return $files;
    }

    /**
     * @param string $path Dir path
     * @return bool
     */
    protected function recursiveDelete($path)
    {
        $files = $this->ls($path);

        foreach ($files as $file) {
            if ($file->getType() == 'dir') {
                $this->recursiveDelete($path . '/' . $file->getFilename());
            } else {
                $this->delete($path . '/' . $file->getFilename());
            }
        }

        return $this->delete($path);
    }

    /**
     * @param string $path
     * @return string Remote URI
     */
    protected function getRemoteURI($path)
    {
        $path = ltrim($path, '/');
        return '/' . $this->bucket . '/' . $path;
    }

    protected function mergeDefaults($defaults, $options)
    {
        return array_merge($defaults, $options);
    }

    protected function performRequest($method, $path, array $options = [])
    {
        $uri = $this->getRemoteURI($path);
        $guzzle = new Guzzle();

        $defaults = ['exceptions' => false, 'debug' => $this->debug];
        $options = $this->mergeDefaults($defaults, $options);

        /**
         * Handle file
         */
        $filesize = 0;
        if (isset($options['file'])) {
            $options['body'] = Stream::factory($options['file']);
            $filesize = $options['body']->getSize();
        } elseif (isset($options['content'])) {
            $options['body'] = $options['content'];
            $filesize = strlen($options['body']);
        }

        /**
         * Handler folder
         */
        $folder = isset($options['folder']) && $options['folder'];
        unset($options['file']);
        unset($options['folder']);
        unset($options['content']);

        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $sign = md5("{$method}&{$uri}&{$date}&{$filesize}&" . md5($this->password));

        $request = $guzzle->createRequest($method, $this->endpoint . $uri, $options);
        $request->setHeader('Authorization', 'UpYun ' . $this->username . ':' . $sign);
        $request->setHeader('Date', $date);
        $request->setHeader('Expect', '');
        if ($folder) {
            $request->setHeader('Folder', 'true');
        }

        $response = $guzzle->send($request);
        return $response;
    }

    /**
     * @param $response \GuzzleHttp\Message\ResponseInterface
     * @return bool
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotAcceptableException
     * @throws NotFoundException
     * @throws ServiceUnavailableException
     * @throws UnauthorizationException
     * @throws UnknownException
     */
    protected function returnOrThrow($response)
    {
        if ($this->debug) {
            switch ($response->getStatusCode()) {
                case 200:
                    return true;
                    break;
                case 400:
                    throw new BadRequestException($response->getReasonPhrase());
                    break;
                case 401:
                    throw new UnauthorizationException($response->getReasonPhrase());
                    break;
                case 403:
                    throw new ForbiddenException($response->getReasonPhrase());
                    break;
                case 404:
                    throw new NotFoundException($response->getReasonPhrase());
                    break;
                case 406:
                    throw new NotAcceptableException($response->getReasonPhrase());
                    break;
                case 503:
                    throw new ServiceUnavailableException($response->getReasonPhrase());
                    break;
                default:
                    throw new UnknownException($response->getReasonPhrase());
                    break;
            }
        } else {
            return $response->getStatusCode() == 200;
        }
    }
}
