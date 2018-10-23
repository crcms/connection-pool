<?php

namespace CrCms\Foundation\ConnectionPool\Contracts;

/**
 * Interface ConnectionFactory
 * @package CrCms\Foundation\ConnectionPool\Contracts
 */
interface ConnectionFactory
{
    /**
     * @param string $name
     * @return Connection
     */
    public function make(string $name): Connection;
}