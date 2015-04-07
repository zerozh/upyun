<?php
namespace Upyun\Util;

use OutOfBoundsException;

class Directory extends FileInfo implements \Countable, \SeekableIterator
{
    protected $files = [];
    protected $position;
    protected $recursive = false;

    public function __construct($files, $recursive = false)
    {
        $this->files = $files;
        $this->recursive = $recursive;
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        if ($this->recursive && $this->files[$this->position]['type'] == 'dir') {
            return new Directory($this->files[$this->position]['files']);
        }
        return new FileInfo($this->files[$this->position]);
    }

    public function seek($position)
    {
        if (!isset($this->files[$position])) {
            throw new OutOfBoundsException("invalid seek position ($position)");
        }

        $this->position = $position;
    }

    public function valid()
    {
        return array_key_exists($this->position, $this->files);
    }

    public function count()
    {
        return count($this->files);
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
