<?php

namespace CrCms\Foundation\ConnectionPool;

use BadMethodCallException;
use CrCms\Foundation\ConnectionPool\Contracts\Connection;

/**
 * Class ConnectionManager
 * @package CrCms\Foundation\ConnectionPool
 */
class ConnectionManager
{
    /**
     * @var PoolManager
     */
    protected $manager;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * ConnectionManager constructor.
     * @param PoolManager $manager
     */
    public function __construct(PoolManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param null $name
     * @return ConnectionManager
     */
    public function connection($name = null): self
    {
        $this->connection = $this->manager->connection($name);
        return $this;
    }

    /**
     * @return void
     */
    public function disconnection(): void
    {
        $this->manager->disconnection($this->connection);
        $this->connection = null;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (method_exists($this->connection, $method)) {
            return $this->connection->{$method}(...$arguments);
        }

        throw new BadMethodCallException("The method[{$method}] is not exists");
    }
}