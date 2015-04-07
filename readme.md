# Upyun 客户端

## 安装

Composer 安装：`"zerozh\upyun" : "dev-master"`

本包可能会与其他使用 `\Upyun` Namespace 的包冲突。

## 使用

请先准备又拍云的操作员帐号，密码，操作空间（Bucket）

### 文件
```PHP
$client = new \Upyun\Client([
    'username' => 'OPERATOR_USERNAME',
    'password' => 'OPERATOR_PASSWORD', 
    'bucket' => 'BUCKET'
]);

// 上传文件
// 可以使用文件资源，本地文件路径或纯文本直接创建文件
// 传入字符串时，若该字符串为有效的本地文件路径，优先使用，否则为文本创建文件
$client->put('somefile.jpg', '/from/local/path/somefile.jpg');
$client->put('another.png', fopen('/from/local/path/another.png', 'r'));
$client->put('also.txt', 'Hello World');

// 获取文件信息
$fileinfo = $client->head('somefile.jpg');
//返回 `\Upyun\Util\FileInfo` 实例，支持的方法如下，参数和返回结果与 SplFileInfo 相同。
echo $fileinfo->getFilename(); // string somefile.jpg
echo $fileinfo->getMTime(); // int UNIX Timestamp
echo $fileinfo->getSize(); // int file size
echo $fileinfo->getType(); // string: file|dir

// 删除文件
$client->delete('somefile.jpg');
```

### 文件夹

```PHP
$client = new \Upyun\Client([
    'username' => 'OPERATOR_USERNAME',
    'password' => 'OPERATOR_PASSWORD', 
    'bucket' => 'BUCKET'
]);

// 创建文件夹
$client->mkdir('folder/subfolder');

// 添加一些文件
$client->put('folder/subfolder/1.jpg', '/from/local/path/1.jpg');
$client->put('folder/subfolder/2.jpg', '/from/local/path/2.jpg');
$client->put('folder/subfolder/3.jpg', '/from/local/path/3.jpg');

// 遍历文件夹
$files = $client->ls('folder/subfolder');
foreach($files as $file){
    // 每个文件都为 `\Upyun\Util\FileInfo` 实例
    echo $file->getSize();
    // 如果遇到文件夹，需要手动遍历
    if ($file->isDir()) {
        $files = $client->ls('folder/subfolder/'. $file->getFilename()) . "\n";
    }
}
```

### 递归文件夹（测试）

```PHP
$client = new \Upyun\Client([
    'username' => 'OPERATOR_USERNAME',
    'password' => 'OPERATOR_PASSWORD', 
    'bucket' => 'BUCKET'
]);

// 添加文件
$client->mkdir('folder/subfolder1');
$client->put('folder/subfolder2/json.json', '{"ping":"pong"}');
$client->put('folder/subfolder3.txt', 'Hello World');

// 遍历文件夹
$files = $client->ls('folder/subfolder', **true**);
foreach($files as $file){
    if ($file->isDir()) {
        foreach($file as $subfile){
            echo $file->getFilename() . "/" . $subfile->getFilename() . "\n";
        }
    } else {
        echo $file->getFilename() . ' ' . $file->getSize() . "\n";
    }
}

// 删除文件夹（递归删除所有内容）
$client->rmrf('folder');
```

## 功能
* 暂未支持高级图片上传设置
