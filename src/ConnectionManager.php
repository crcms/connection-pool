<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/25 6:25
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\ConnectionPool;

use CrCms\Foundation\ConnectionPool\Contracts\Connection;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionFactory;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionPool;
use Illuminate\Container\Container;
use InvalidArgumentException;
use RuntimeException;
use RangeException;
use OutOfRangeException;

/**
 * Class ConnectionManager
 * @package CrCms\Foundation\ConnectionPool
 */
class ConnectionManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $pools = [];

    /**
     * @var array
     */
    protected $poolConfig = [
        'pool' => [
            'max_idle_number' => 1000,//最大空闲数
            'min_idle_number' => 100,//最小空闲数
            'max_connection_number' => 800,//最大连接数
            'max_connection_time' => 1,//最大连接时间
        ]
    ];

    /**
     * ConnectionManager constructor.
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @param null $name
     * @return Connection
     */
    public function connection($name = null): Connection
    {
        $resolve = $this->resolveConfig($name);
        /* @var string $name */
        /* @var array $configure */
        list($name, $configure) = array_values($resolve);

        $this->createPoolIfNotExists($name, $configure);

        if ($this->pools[$name]->getTasksCount() > $configure['max_connection_number']) {
            throw new RuntimeException('More than the maximum number of connections');
        }

        $this->fillConnectionIfNotExists($name, $configure);

        return $this->effectiveConnection($name, $this->pools[$name]);
    }

    /**
     * @param null $name
     * @return array
     */
    protected function resolveConfig($name = null): array
    {
        if (is_array($name)) {
            list($name, $configure) = [$name['name'] ?? $this->defaultDriver(), $name];
        } else {
            $name = $name ? $name : $this->defaultDriver();
            $configure = $this->configuration($name);
        }

        return compact('name', 'configure');
    }

    /**
     * @return Connection
     */
    protected function effectiveConnection(string $name, ConnectionPool $pool): Connection
    {
        while ($pool->has()) {
            $connection = $pool->get();

            //激活当前连接
            $connection->makeActive();

            //断线重连机制
            if (!$connection->isAlive()) {
                $connection->reconnect();
                //二次重连失败，直接销毁
                if (!$connection->isAlive()) {
                    $pool->destroy($connection);
                    continue;
                }
            }

            return $connection;
        }

        throw new RangeException("No valid connections found");
    }

    /**
     * @param Connection $connection
     * @return bool
     */
    protected function activityTimeout(Connection $connection): bool
    {
        return (time() - $connection->getLastActivityTime()) > $this->poolConfig['max_connection_time'];
    }

    /**
     * @param $name
     * @param array $configure
     * @param ConnectionPool $pool
     * @return void
     */
    protected function makeConnections($name, array $configure, ConnectionPool $pool): void
    {
        /* @var ConnectionFactory $factory */
        if (!class_exists($configure['factory'])) {
            throw new OutOfRangeException("The facotry[{$configure['factory']}] not found");
        }
        $factory = $this->app->make($configure['factory']);

        /* @var array $options */
        $options = $this->app->make('config')->get("{$name}.connections.{$configure['connection']}");
        if (empty($options)) {
            throw new OutOfRangeException("The driver[{$name}] connection not found");
        }

        $count = min(
            $configure['max_idle_number'] - $pool->getIdleQueuesCount(),
            $configure['min_idle_number'] + $pool->getIdleQueuesCount()
        );
        while ($count) {
            $pool->put($factory->make($options));
            $count -= 1;
        }
    }

    /**
     * @param string $name
     * @param array $configure
     * @return void
     */
    protected function createPoolIfNotExists(string $name, array $configure): void
    {
        if (!isset($this->pools[$name])) {
            $this->makeConnections($name, $configure, $this->app->make('pool.pool'));
        }
    }

    /**
     * @param string $name
     * @param array $configure
     */
    protected function fillConnectionIfNotExists(string $name, array $configure)
    {
        if (!$this->pools[$name]->has()) {
            $this->makeConnections($name, $configure, $this->pools[$name]);
        }
    }

    /**
     * @return string
     */
    protected function defaultDriver(): string
    {
        return $this->app->make('config')->get('pool.default');
    }

    /**
     * @param string $name
     * @return array
     */
    protected function configuration($name): array
    {
        $connections = $this->app->make('config')->get('pool.connections');

        if (!isset($connections[$name])) {
            return $this->poolConfig;
            //throw new InvalidArgumentException("Pool config[{$name}] not found");
        }

        return array_merge($this->poolConfig, $connections[$name]);
    }

    /**
     * @param Connection $connection
     */
    public function disconnection(Connection $connection): void
    {
        if (!$connection->isAlive()) {
            $this->pool->destroy($connection);
            return;
        }

        if (
            $connection->isRelease() ||
            $this->activityTimeout($connection)
        ) {
            $this->pool->release($connection);
        }
    }
}