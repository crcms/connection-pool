<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-03-22 22:35
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\ConnectionPool\Tests;

use CrCms\Foundation\ConnectionPool\ConnectionPool;
use CrCms\Foundation\ConnectionPool\Contracts\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionPoolTest extends TestCase
{

    public function testA()
    {
        $pool = new ConnectionPool(101);

        $z = go(function() use ($pool){
            for ($i = 0;$i<=100;$i++) {
                $pool->put(\Mockery::mock(Connection::class));
            }

            //dd(12,$pool->getIdleQueuesCount());

            \Co::sleep(1);
            return 123;
        });

        dump($z);


    }

}