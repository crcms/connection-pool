<?php

namespace CrCms\Foundation\ConnectionPool\Contracts;

/**
 * Interface ConnectionFactory
 * @package CrCms\Foundation\ConnectionPool\Contracts
 */
interface ConnectionFactory
{
    /**
     * @return Connection
     */
    public function make(array $config): Connection;
}