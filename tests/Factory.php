<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/26 6:13
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\ConnectionPool\Tests;

use CrCms\Foundation\ConnectionPool\AbstractConnectionFactory;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionPool as ConnectionPoolContract;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionFactory as ConnectionFactoryContract;
use CrCms\Foundation\ConnectionPool\Contracts\Connection as ConnectionContract;
use CrCms\Foundation\ConnectionPool\Contracts\Connector as ConnectorContract;
use CrCms\Foundation\Client\Http\Guzzle\Connection as GuzzleConnection;
use CrCms\Foundation\Client\Http\Guzzle\Connector as GuzzleConnector;
use CrCms\Foundation\Client\Http\Swoole\Connection as SwooleConnection;
use CrCms\Foundation\Client\Http\Swoole\Connector as SwooleConnector;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Class Factory
 * @package CrCms\Foundation\Rpc\Client
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