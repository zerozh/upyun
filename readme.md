# Upyun 客户端

## 安装

Composer 安装：`"zerozh\upyun" : "dev-master"`

本包可能会与其他使用 `Upyun` Namespace 的包冲突。

## 使用

请先准备又拍云的操作员帐号，密码，操作空间（Bucket）

```PHP
$client = new \Upyun\Client(['username' => 'OPERATOR_USERNAME', 'password' => 'OPERATOR_PASSWORD', 'bucket' => 'BUCKET']);

// 将本地文件 /local/path/somefile.jpg 上传到空间中的根目录下 'somefile.jpg
$client->put('somefile.jpg', '/local/path/somefile.jpg');
// 暂时只支持文件名，不支持直接传文件内容

//删除文件
$client->delete('somefile.jpg');
```

