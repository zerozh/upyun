<?php
namespace Upyun;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Stream\Stream;
use Upyun\Exceptions\BadRequestException;

class Client
{
    const VERSION = '0.0.1';

    protected $bucket;
    protected $username;
    protected $password;

    protected $debug = false;
    protected $endpoint = 'http://v0.api.upyun.com';
    protected $http_timeout = 30;

    public function __construct(array $options = [])
    {
        if (!isset($options['username']) || !isset($options['password'])) {
            throw new BadRequestException(_('You must fill username and password'));
        }

        if (isset($options['bucket'])) {
            $this->bucket = $options['bucket'];
        }
        $this->username = $options['username'];
        $this->password = $options['password'];
    }

    public function version()
    {
        return self::VERSION;
    }

    public function mkdir($path, array $options = [])
    {
        //
    }

    /**
     * @param string $path Remote file path, begin with `/`
     * @param string $file Local file path
     * @param array $options
     */
    public function put($path, $file, array $options = [])
    {
        $this->performRequest('PUT', $this->getRemotePath($path), ['file' => $file]);
    }

    public function delete($path, array $options = [])
    {
        $this->performRequest('DELETE', $this->getRemotePath($path));
    }

    /**
     * @param string $path
     * @return string Remote Path with Bucket name
     */
    protected function getRemotePath($path)
    {
        return '/' . $this->bucket . $path;
    }

    protected function mergeOptions($defaults, $options)
    {
        return array_merge($defaults, $options);
    }

    protected function performRequest($method, $uri, array $options = [])
    {
        $guzzle = new Guzzle();

        $defaults = [];
        if ($this->debug) {
            $defaults = ['debug' => true];
        }
        $options = $this->mergeOptions($defaults, $options);

        if (isset($options['file'])) {
            $resource = fopen($options['file'], 'r');
            $options['body'] = Stream::factory($resource);
            $filesize = $options['body']->getSize();
            unset($options['file']);
        } else {
            $filesize = 0;
        }

        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $sign = md5("{$method}&{$uri}&{$date}&{$filesize}&" . md5($this->password));

        $request = $guzzle->createRequest($method, $this->endpoint . $uri, $options);
        $request->setHeader("Expect", '');
        $request->setHeader("Date", $date);
        $request->setHeader("Authorization", "UpYun {$this->username}:" . $sign);

        $response = $guzzle->send($request);
        return $response;
    }
}
