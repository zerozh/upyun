<?php
namespace Upyun\Util;

class Directory
{
    protected $files = [];

    public function __construct($files)
    {
        foreach ($files as $file) {
            $this->files[] = new FileInfo($file);
        }
    }

    /**
     * Get All files
     * @return \Upyun\Util\FileInfo[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Read file list from response body
     * @param string $body
     * @return array
     */
    public static function parseBody($body)
    {
        if (!$body) {
            return [];
        }
        $result = [];
        $files = explode("\n", $body);
        foreach ($files as $file) {
            $tmp = explode("\t", $file);
            if (count($tmp) < 4) {
                /**
                 * Throw error on malformed format
                 */
                continue;
            }
            $item = [];
            $item['filename'] = $tmp[0];
            $item['type'] = $tmp[1] == 'N' ? 'file' : 'dir';
            $item['size'] = (int)$tmp[2];
            $item['mtime'] = (int)$tmp[3];
            $result[] = $item;
        }
        return $result;
    }
}
