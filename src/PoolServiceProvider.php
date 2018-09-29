<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/28 20:42
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\ConnectionPool;

use CrCms\Foundation\ConnectionPool\Contracts\ConnectionPool as ConnectionPoolContract;
use Illuminate\Support\ServiceProvider;

class PoolServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * @var string
     */
    protected $namespaceName = 'pool';

    /**
     * @var string
     */
    protected $packagePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    /**
     * @return void
     */
    public function boot()
    {
        //move config path
        $this->publishes([
            $this->packagePath . 'config' => config_path(),
        ]);
    }

    /**
     * @return void
     */
    public function register(): void
    {
        //merge config
        $configFile = $this->packagePath . "config/config.php";
        if (file_exists($configFile)) $this->mergeConfigFrom($configFile, $this->namespaceName);

        $this->registerAlias();

        $this->registerConnectionServices();
    }

    /**
     *
     */
    protected function registerConnectionServices()
    {
        $this->app->bind('pool.pool',ConnectionPool::class);

        $this->app->singleton('pool.manager', function ($app) {
            return new ConnectionManager($app);
        });
    }

    /**
     * @return void
     */
    protected function registerAlias(): void
    {
        $this->app->alias('pool.pool', ConnectionPoolContract::class);
        $this->app->alias('pool.manager', ConnectionManager::class);
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return [
            'pool.pool',
            'pool.manager'
        ];
//        return parent::provides();
    }
}