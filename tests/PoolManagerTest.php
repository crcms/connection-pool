<?php

namespace CrCms\Foundation\ConnectionPool\Tests;

use CrCms\Tests\CreatesApplication;
use Illuminate\Foundation\Testing\TestCase;

/**
 * Class PoolManagerTest
 * @package CrCms\Foundation\ConnectionPool\Tests
 */
class PoolManagerTest extends TestCase
{

    use CreatesApplication;

    protected function config()
    {
        config([
            'client.connections.http' => [
                'driver' => 'http',
                'host' => 'crcms.cn',
                'port' => 80,
                'settings' => [
                    'timeout' => 1,
                ],
            ]
        ]);

        config([
            'pool.connections.client.factory' => \CrCms\Foundation\ConnectionPool\Tests\Factory::class,
        ]);
    }

    public function testConnection()
    {
        $this->config();
        $this->app->make('pool.manager')->connection('client');
    }

}