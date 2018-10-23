<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/26 6:13
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Tests\ConnectionPool;

use CrCms\Foundation\ConnectionPool\Contracts\ConnectionFactory as ConnectionFactoryContract;
use CrCms\Foundation\ConnectionPool\Contracts\Connection as ConnectionContract;
use InvalidArgumentException;

/**
 * Class Factory
 * @package CrCms\Foundation\ConnectionPool\Tests
 */
class Factory implements ConnectionFactoryContract
{
    /**
     * @return ConnectionContract
     */
    public function make(string $name): ConnectionContract
    {
        return $this->createConnection($name);
    }

    /**
     * @param array $config
     * @return ConnectionContract
     */
    protected function createConnection(string $name): ConnectionContract
    {
        $config = [];

        switch ($name) {
            case 'client':
                return new Connection([
                    'host' => '',
                    'port' => '',
                    'settings' => [
                        'timeout' => 1,
                    ],
                ]);
        }

        throw new InvalidArgumentException("Unsupported driver [{$name}]");
    }
}