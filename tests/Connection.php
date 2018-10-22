<?php

namespace CrCms\Foundation\ConnectionPool\Tests;

use CrCms\Foundation\ConnectionPool\AbstractConnection;
use CrCms\Foundation\ConnectionPool\Contracts\Connector;
use CrCms\Foundation\ConnectionPool\Contracts\Connection as ConnectionContract;
use GuzzleHttp\Client;

/**
 * Class Connection
 * @package CrCms\Foundation\Tests
 */
class Connection extends AbstractConnection implements ConnectionContract
{
    /**
     * @var Client
     */
    protected $connector;

    public function connect(): Client
    {
        $settings = $this->config;
        $settings['base_uri'] = $this->baseUri($this->config);
        return new Client($settings);
    }

    /**
     * @param string $scheme
     * @param array $config
     * @return string
     */
    protected function baseUri(string $scheme, array $config): string
    {
        return $scheme . '://' . $config['host'] . ':' . $config['port'];
    }
}