<?php
namespace Upyun\Util;

class FileInfo
{
    protected $filename = '';
    protected $mtime = 0;
    protected $size = 0;
    protected $type;

    public function __construct($file, $extra = [])
    {
        $file = array_merge($file, $extra);
        $this->filename = isset($file['filename']) ? $file['filename'] : '';
        $this->mtime = isset($file['mtime']) ? $file['mtime'] : 0;
        $this->size = isset($file['size']) ? $file['size'] : 0;
        $this->type = isset($file['type']) ? $file['type'] : null;
    }

    /**
     * @return string filename
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string file or dir
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int Last Modified time
     */
    public function getMTime()
    {
        return $this->mtime;
    }

    /**
     * @return int File size
     */
    public function getSize()
    {
        return $this->size;
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
