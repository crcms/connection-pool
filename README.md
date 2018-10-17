# 远程调用的统一客户端

## 使用方法

### 加载引入

在`config/app.php`中增加
```
'providers' => [
    CrCms\Foundation\ConnectionPool\PoolServiceProvider::class,
]
```

### 增加配置

在`config/pool.php`的connections中增加如下测试配置
```
'client' => [
    'max_idle_number' => 50,//最大空闲数
    'min_idle_number' => 15,//最小空闲数
    'max_connection_number' => 20,//最大连接数
    'max_connection_time' => 3,//最大连接时间(s)
],
```

### 调用方法

#### ConnectionFactoryContract的实现
目前已在`Client`模块中实现了`Client`的工厂方法,`Redis`,`Mysql`等连接池使用，请参考`Client`模块工厂

```
/* @var ConnectionManager $manager */
$manager = $this->app->make('pool.manager');

$factory = $this->app->make('client.factory');

//获取当前连接  client:客户端连接池配置
$connection = $manager->connection($factory, 'client');
//发送请求
$connection->request('baidu.com',[]);
//获取当前的连接资源的响应
dump(get_class($connection->getResponse()));
//获取资源响应内容
dump($connection->getContent());
//资源回收
$manager->close($connection);
```

### 动态化配置

连接池也支持运行动态化加载配置，如下示例：
```
/* @var ConnectionManager $manager */
$manager = $this->app->make('pool.manager');

$factory = $this->app->make('client.factory');

//获取当前连接  client:客户端连接池配置
$connection = $manager->connection($factory, 'custom');
//发送请求
$connection->request('baidu.com',[]);
//获取当前的连接资源的响应
dump(get_class($connection->getResponse()));
//获取资源响应内容
dump($connection->getContent());
//资源回收
$manager->close($connection);
```

当连接池使用动态自定义连接名称时，则系统会使用默认的连接参数，如：

```
$connection = $manager->connection($factory, 'custom');
```

如果传入数组配置，则会优先以动态配置覆盖原默认配置

```
$connection = $manager->connection($factory, [
    'name' => 'custom',
    'max_idle_number' => 50,//最大空闲数
    'min_idle_number' => 15,//最小空闲数
    'max_connection_number' => 20,//最大连接数
    'max_connection_time' => 3,//最大连接时间(s)
]);
```

### 支持的类型
- Client 客户端
