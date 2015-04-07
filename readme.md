# Upyun 客户端

## 安装

Composer 安装：`"zerozh\upyun" : "dev-master"`

本包可能会与其他使用 `\Upyun` Namespace 的包冲突。

## 使用

请先准备又拍云的操作员帐号，密码，操作空间（Bucket）

```PHP
$client = new \Upyun\Client([
    'username' => 'OPERATOR_USERNAME',
    'password' => 'OPERATOR_PASSWORD', 
    'bucket' => 'BUCKET'
]);

// 上传文件
$client->put('somefile.jpg', '/from/local/path/somefile.jpg');
// 暂时只支持文件名，不支持直接传文件内容

// 获取文件信息
$fileinfo = $client->head('somefile.jpg');
/**
 * 返回 `\Upyun\Util\FileInfo` 对象，支持的方法如下，参数和返回结果与 SplFileInfo 相同。
 */
echo $fileinfo->getFilename(); // somefile.jpg
echo $fileinfo->getMTime(); // Last Modified UNIX Timestamp
echo $fileinfo->getSize(); // integer file size
echo $fileinfo->getType(); // file or dir

// 删除文件
$client->delete('somefile.jpg');

// 创建文件夹
$client->mkdir('folder/subfolder');

```

