<?php

class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected $options = [];

    public function testHasParam()
    {
        global $argc;
        $this->assertGreaterThan(4, $argc, 'No Username, Password, Bucket passed.
        Use phpunit ./test/ClientTest.php USERNAME PASSWORD BUCKET');
    }

    public function testInit()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
    }

    public function testUploadViaContent()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->put('test.txt', 'Hello World');
        $this->assertTrue($response);
    }

    public function testUploadViaPath()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->put('test.png', __DIR__ . '/Data/test.png');
        $this->assertTrue($response);
    }

    public function testUploadViaResource()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->put('test.jpg', fopen(__DIR__ . '/Data/test.jpg', 'r'));

        $this->assertTrue($response);
    }

    public function testStatus()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->head('test.png');
        $this->assertInstanceOf('\Upyun\Util\FileInfo', $response);
        $this->assertEquals(filesize(__DIR__ . '/Data/test.png'), $response->getSize());
        $this->assertEquals('file', $response->getType());


        $response = $client->head('test.jpg');
        $this->assertInstanceOf('\Upyun\Util\FileInfo', $response);
        $this->assertEquals(filesize(__DIR__ . '/Data/test.jpg'), $response->getSize());
        $this->assertEquals('file', $response->getType());
    }

    public function testDownloadFile()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->get('test.txt');
        $this->assertEquals('Hello World', $response);
    }

    public function testDelete()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->delete('test.png');
        $this->assertTrue($response);

        $response = $client->delete('test.jpg');
        $this->assertTrue($response);

        $response = $client->delete('test.txt');
        $this->assertTrue($response);
    }

    public function testDirMkdir()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->mkdir('custom_dir');
        $this->assertTrue($response);
    }

    public function testDirStatus()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->head('custom_dir');
        $this->assertInstanceOf('\Upyun\Util\FileInfo', $response);
        $this->assertEquals(0, $response->getSize());
        $this->assertEquals('dir', $response->getType());
    }

    public function testDirDelete()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->delete('custom_dir');
        $this->assertTrue($response);
    }

    public function testMultiDirMkdir()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->mkdir('complex/apple');
        $this->assertTrue($response);

        $response = $client->mkdir('complex/orange/blue');
        $this->assertTrue($response);

        $response = $client->mkdir('complex/orange/red');
        $this->assertTrue($response);

        $response = $client->put('complex/banana/yellow/hit.txt', 'Raw Content Here');
        $this->assertTrue($response);

        $response = $client->put('complex/banana/yellow/bit.md', '# Markdown Example');
        $this->assertTrue($response);

        $response = $client->mkdir('complex/banana/yellow/eat');
        $this->assertTrue($response);

        $response = $client->put('complex/strawberry/red.png', __DIR__ . '/Data/test.png');
        $this->assertTrue($response);

        $response = $client->put('complex/strawberry/pink.txt', 'Text Test');
        $this->assertTrue($response);

        $response = $client->put('complex/pear.json', '{"ping":"pong"}');
        $this->assertTrue($response);
    }

    public function testMultiDirList()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->ls('complex');
        $this->assertInstanceOf('\Upyun\Util\Directory', $response);
        foreach ($response as $item) {
            $this->assertInstanceOf('\Upyun\Util\FileInfo', $item);
        }

        $response = $client->ls('complex/orange');
        $this->assertInstanceOf('\Upyun\Util\Directory', $response);
        foreach ($response as $item) {
            $this->assertTrue($item->isDir());
        }

        $response = $client->ls('complex/strawberry');
        $this->assertInstanceOf('\Upyun\Util\Directory', $response);
        foreach ($response as $item) {
            $this->assertTrue($item->isFile());
        }
    }

    public function testMultiDirRecursiveList()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->ls('complex', true);
        foreach ($response as $n) {
            if ($n->isDir()) {
                $this->assertInstanceOf('\Upyun\Util\Directory', $n);
                foreach ($n as $j) {
                    $this->assertInstanceOf('\Upyun\Util\FileInfo', $j);
                    if ($j->isDir()) {
                        foreach ($j as $_j) {
                            $this->assertInstanceOf('\Upyun\Util\FileInfo', $_j);
                        }
                    }
                }
            } else {
                $this->assertInstanceOf('\Upyun\Util\FileInfo', $n);
            }
        }
    }

    public function testMultiDirDelete()
    {
        global $argv;
        $options = [
            'bucket' => $argv[4],
            'username' => $argv[2],
            'password' => $argv[3],
        ];
        $client = new Upyun\Client($options);
        $response = $client->rmrf('complex');
        $this->assertTrue($response);
    }
}
