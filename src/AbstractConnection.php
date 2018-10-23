<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/25 6:46
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\ConnectionPool;

use CrCms\Foundation\ConnectionPool\Contracts\Connection as ConnectionContract;
use CrCms\Foundation\ConnectionPool\Contracts\Connector;
use BadMethodCallException;
use CrCms\Foundation\ConnectionPool\Exceptions\ConnectionException;
use Exception;

/**
 * Class AbstractConnection
 * @package CrCms\Foundation\ConnectionPool
 */
abstract class AbstractConnection implements ConnectionContract
{
    /**
     * @var mixed
     */
    protected $response;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var array
     */
    protected $config;

    /**
     * 是否是存活链接
     *
     * @var bool
     */
    protected $isAlive = true;

    /**
     * 最后活动时间
     *
     * @var int
     */
    protected $lastActivityTime = 0;

    /**
     * 连接次数
     *
     * @var int
     */
    protected $connectionNumber = 0;

    /**
     * AbstractConnection constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connector = $this->reconnect();
        $this->updateLastActivityTime();
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return spl_object_hash($this);
    }

    /**
     * @return int
     */
    public function getLastActivityTime(): int
    {
        return $this->lastActivityTime;
    }

    /**
     * @return int
     */
    public function getConnectionNumber(): int
    {
        return $this->connectionNumber;
    }

    /**
     * @return bool
     */
    public function isAlive(): bool
    {
        return $this->isAlive;
    }

    /**
     * @return void
     */
    public function makeAlive(): void
    {
        $this->isAlive = true;
    }

    /**
     * @return void
     */
    public function makeDead(): void
    {
        $this->isAlive = false;
    }

    /**
     * @return void
     */
    protected function updateLastActivityTime(): void
    {
        $this->lastActivityTime = time();
    }

    /**
     * @return void
     */
    protected function increaseConnectionNumber(): void
    {
        $this->connectionNumber += 1;
    }

    /**
     * @return mixed
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * @return void
     */
    public function reconnect(): void
    {
        $this->connector = $this->connect();
        $this->makeAlive();
    }

    /**
     * @return void
     */
    public function disconnect(): void
    {
        $this->connector = null;
        $this->makeDead();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->connector, $name)) {
            return call_user_func_array([$this->connector, $name], $arguments);
        }

        throw new BadMethodCallException("The method[{$name}] is not exists");
    }
}