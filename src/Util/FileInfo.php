<?php
namespace Upyun\Util;

class FileInfo
{
    protected $file;

    public function __construct($file, $extra = [])
    {
        $file = array_merge($file, $extra);
        $this->initFile($file);
    }

    protected function initFile($file)
    {
        $this->file['filename'] = isset($file['filename']) ? $file['filename'] : '';
        $this->file['mtime'] = isset($file['mtime']) ? $file['mtime'] : 0;
        $this->file['size'] = isset($file['size']) ? $file['size'] : 0;
        $this->file['type'] = isset($file['type']) ? $file['type'] : null;
    }

    /**
     * @return bool
     */
    public function isDir()
    {
        return $this->file['type'] == 'dir';
    }

    /**
     * @return bool
     */
    public function isFile()
    {
        return $this->file['type'] == 'file';
    }

    /**
     * @return string filename
     */
    public function getFilename()
    {
        return $this->file['filename'];
    }

    /**
     * @return string file or dir
     */
    public function getType()
    {
        return $this->file['type'];
    }

    /**
     * @return int Last Modified time
     */
    public function getMTime()
    {
        return $this->file['mtime'];
    }

    /**
     * @return int File size
     */
    public function getSize()
    {
        return $this->file['size'];
    }

    /**
     * Read file info from response header
     * @param array $params
     * @return array
     */
    public static function parseHeader($params)
    {
        $result = [];
        if (isset($params['x-upyun-file-date'][0])) {
            $result['mtime'] = (int)$params['x-upyun-file-date'][0];
        }

        if (isset($params['x-upyun-file-type'][0])) {
            if ($params['x-upyun-file-type'][0] == 'folder') {
                $result['type'] = 'dir';
            } else {
                $result['type'] = 'file';
            }
        }

        if (isset($params['x-upyun-file-size'][0])) {
            $result['size'] = (int)$params['x-upyun-file-size'][0];
        }
        return $result;
    }
}
