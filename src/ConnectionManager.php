<?php

namespace CrCms\Foundation\ConnectionPool;

use BadMethodCallException;
use CrCms\Foundation\ConnectionPool\Contracts\Connection;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionFactory;

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
     * @var string
     */
    protected $name;

    /**
     * ConnectionManager constructor.
     * @param PoolManager $manager
     */
    public function __construct(PoolManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param ConnectionFactory $factory
     * @param null $name
     * @return ConnectionManager
     */
    public function connection(ConnectionFactory $factory, $name = null): self
    {
        if (is_null($this->connection)) {
            $this->name = $name;
            $this->connection = $this->manager->connection($factory, $name);
        }

        return $this;
    }

    /**
     * @return void
     */
    public function disconnection(): void
    {
        $this->manager->disconnection($this->connection, $this->name);
        $this->connection = null;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
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