<?php

namespace CrCms\Tests\ConnectionPool;

use CrCms\Foundation\ConnectionPool\ConnectionManager;
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
            'pool.connections.client.factory' => \CrCms\Tests\ConnectionPool\Factory::class,
            'pool.connections.client.pool' => [
                'max_idle_number' => 1000,//最大空闲数
                'min_idle_number' => 10,//最小空闲数
                'max_connection_number' => 10,
            ],
        ]);
    }

    public function testConnection()
    {
        $this->config();
        /* @var ConnectionManager $manager */
        $manager = $this->app->make('pool.manager');
        $connection = $manager->connection('client');
        $this->assertInstanceOf(Connection::class,$connection);
        dump('Connection Before Alive:true');
        $this->assertEquals(true,$connection->isAlive());

        dump('Start Connection');
        $connection->handle();

//        dump('Connection Before Alive:true Release:true');
//        $this->assertEquals(true,$connection->isAlive());
//        $this->assertEquals(true,$connection->isRelease());
        //$manager->disconnection($connection,'client');
        $this->assertEquals(1,$manager->getPool('client')->getTasksCount());
        $this->assertEquals(9,$manager->getPool('client')->getIdleQueuesCount());
        $manager->disconnection($connection,'client');
        $this->assertEquals(0,$manager->getPool('client')->getTasksCount());
        $this->assertEquals(10,$manager->getPool('client')->getIdleQueuesCount());

        $connections = [];
        for ($i=0;$i<=8;$i++) {
            $connections[] = ($manager->connection('client'));
        }

        $this->assertEquals(9,$manager->getPool('client')->getTasksCount());
        $this->assertEquals(1,$manager->getPool('client')->getIdleQueuesCount());

        foreach ($connections as $connection) {
            $manager->disconnection($connection,'client');
        }

        $this->assertEquals(0,$manager->getPool('client')->getTasksCount());
        $this->assertEquals(10,$manager->getPool('client')->getIdleQueuesCount());
    }

}