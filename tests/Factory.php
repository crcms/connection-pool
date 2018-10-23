<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/26 6:13
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Tests\ConnectionPool;

use CrCms\Foundation\ConnectionPool\AbstractConnectionFactory;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionFactory as ConnectionFactoryContract;
use CrCms\Foundation\ConnectionPool\Contracts\Connection as ConnectionContract;
use InvalidArgumentException;

/**
 * Class Factory
 * @package CrCms\Foundation\ConnectionPool\Tests
 */
class Factory extends AbstractConnectionFactory implements ConnectionFactoryContract
{
    /**
     * @return ConnectionContract
     */
    public function make(array $config): ConnectionContract
    {
        return $this->createConnection($config);
    }

    /**
     * @param array $config
     * @return ConnectionContract
     */
    protected function createConnection(array $config): ConnectionContract
    {
        switch ($config['driver']) {
            case 'http':
                return new Connection($config);
        }

        throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
    }
}