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
use Laravel\Lumen\Application;

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
        if ($this->isLumen()) {

        } else {
            $this->publishes([
                $this->packagePath . 'config/config.php' => config_path($this->namespaceName . ".php"),
            ]);
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        if ($this->isLumen()) {
            $this->app->configure($this->namespaceName);
        }

        //merge config
        $configFile = $this->packagePath . "config/config.php";
        if (file_exists($configFile)) $this->mergeConfigFrom($configFile, $this->namespaceName);

        $this->registerAlias();

        $this->registerConnectionServices();
    }

    /**
     * @return void
     */
    protected function registerConnectionServices(): void
    {
        $this->app->bind('pool.pool',ConnectionPool::class);

        $this->app->singleton('pool.manager', function ($app) {
            return new PoolManager($app);
        });
    }

    /**
     * @return void
     */
    protected function registerAlias(): void
    {
        $this->app->alias('pool.pool', ConnectionPoolContract::class);
        $this->app->alias('pool.manager', PoolManager::class);
    }

    /**
     * @return bool
     */
    protected function isLumen(): bool
    {
        return class_exists(Application::class) && $this->app instanceof Application;
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
    }
}