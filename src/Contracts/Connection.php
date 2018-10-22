<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/25 6:33
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\ConnectionPool\Contracts;

/**
 * Interface Connection
 * @package CrCms\Foundation\ConnectionPool\Contracts
 */
interface Connection
{
    /**
     * @return string
     */
    public function id(): string;

    /**
     * @return bool
     */
    public function isRelease(): bool;

    /**
     * @return void
     */
    public function makeRelease(): void;

    /**
     * @return void
     */
    public function makeActive(): void;

    /**
     * @return bool
     */
    public function isAlive(): bool;

    /**
     * @return void
     */
    public function makeAlive(): void;

    /**
     * @return void
     */
    public function makeDead(): void;

    /**
     * @return void
     */
    public function reconnect(): void;

    /**
     * @return mixed
     */
    public function connect();

    /**
     * @return void
     */
    public function disconnect(): void;

    /**
     * @param callable $callable
     * @return mixed
     */
    public function handle(callable $callable);

    /**
     * @return int
     */
    public function getLastActivityTime(): int;

    /**
     * @return int
     */
    public function getConnectionNumber(): int;
}