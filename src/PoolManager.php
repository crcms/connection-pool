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
 * Class PoolManager
 * @package CrCms\Foundation\ConnectionPool
 */
class PoolManager
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
        'max_idle_number' => 50,//最大空闲数
        'min_idle_number' => 3,//最小空闲数
        'max_connection_number' => 50,//最大连接数
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
     * @param ConnectionFactory $factory
     * @param null $name
     * @return Connection
     */
    public function connection(ConnectionFactory $factory, $name = null): Connection
    {
        $resolve = $this->resolveConfig($name);
        /* @var string $name */
        /* @var array $configure */
        list($name, $configure) = array_values($resolve);

        $this->createPoolIfNotExists($name);

        if ($this->pools[$name]->getTasksCount() > $configure['max_connection_number']) {
            throw new RuntimeException('More than the maximum number of connections');
        }

        $this->fillConnectionIfNotExists($name, $configure, $factory);

        return $this->effectiveConnection($this->pools[$name]);
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
    protected function effectiveConnection(ConnectionPool $pool): Connection
    {
        while ($pool->has()) {
            $connection = $pool->get();

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
     * @param $name
     * @param array $configure
     * @param ConnectionPool $pool
     * @param ConnectionFactory $factory
     * @return void
     */
    protected function makeConnections($name, array $configure, ConnectionPool $pool, ConnectionFactory $factory): void
    {
        /* @var ConnectionFactory $factory */
        /*if (!class_exists($configure['factory'])) {
            throw new OutOfRangeException("The facotry[{$configure['factory']}] does not exist");
        }*/
        //$factory = $this->app->make($configure['factory']);

        /* @var array $options */
        /*$options = $this->app->make('config')->get("{$name}.connections.{$configure['connection']}");
        if (empty($options)) {
            throw new OutOfRangeException("The driver[{$name}] connection not found");
        }*/

        $count = min(
            $configure['max_idle_number'] - $pool->getIdleQueuesCount(),
            $configure['min_idle_number'] + $pool->getIdleQueuesCount()
        );
        while ($count) {
            $pool->put($factory->make());
            $count -= 1;
        }
    }

    /**
     * @param string $name
     * @return void
     */
    protected function createPoolIfNotExists(string $name): void
    {
        if (!isset($this->pools[$name])) {
            $this->pools[$name] = $this->app->make('pool.pool');
        }
    }

    /**
     * @param string $name
     * @param array $configure
     * @param ConnectionFactory $factory
     * @return void
     */
    protected function fillConnectionIfNotExists(string $name, array $configure, ConnectionFactory $factory): void
    {
        if (!$this->pools[$name]->has()) {
            $this->makeConnections($name, $configure, $this->pools[$name], $factory);
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
     * @param null $name
     * @return ConnectionPool
     */
    public function getPool($name = null): ConnectionPool
    {
        $resolve = $this->resolveConfig($name);

        return $this->pools[$resolve['name']];
    }

    /**
     * @param Connection $connection
     * @param null $name
     * @return void
     */
    public function disconnection(Connection $connection, $name = null): void
    {
        $resolve = $this->resolveConfig($name);

        $connection->isAlive() ?
            $this->pools[$resolve['name']]->release($connection) :
            $this->pools[$resolve['name']]->destroy($connection);
    }
}