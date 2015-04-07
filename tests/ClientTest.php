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

    public function testUpload()
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
        $response = $client->mkdir('multidir/001');
        $this->assertTrue($response);

        $response = $client->mkdir('multidir/002');
        $this->assertTrue($response);

        $response = $client->mkdir('multidir/003/sub1');
        $this->assertTrue($response);

        $response = $client->mkdir('multidir/003/sub2');
        $this->assertTrue($response);

        $response = $client->put('multidir/003/sub3/test.png', __DIR__ . '/Data/test.png');
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
        $response = $client->ls('multidir');
        $this->assertInstanceOf('\Upyun\Util\Directory', $response);

        $response = $client->ls('multidir/002');
        $this->assertInstanceOf('\Upyun\Util\Directory', $response);

        $response = $client->ls('multidir/003/sub3');
        $this->assertInstanceOf('\Upyun\Util\Directory', $response);
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
        $response = $client->delete('multidir/003/sub3/test.png');
        $this->assertTrue($response);

        $response = $client->delete('multidir/003/sub3');
        $this->assertTrue($response);

        $response = $client->delete('multidir/003/sub2');
        $this->assertTrue($response);

        $response = $client->delete('multidir/003/sub1');
        $this->assertTrue($response);

        $response = $client->delete('multidir/003');
        $this->assertTrue($response);

        $response = $client->delete('multidir/002');
        $this->assertTrue($response);

        $response = $client->delete('multidir/001');
        $this->assertTrue($response);

        $response = $client->delete('multidir');
        $this->assertTrue($response);
    }
}