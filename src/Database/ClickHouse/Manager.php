<?php

namespace Library\Database\ClickHouse;

use ClickHouseDB\Client;
use Library\ConnectionPool;
use Library\Database\Manager as DatabaseManager;
use RuntimeException;

/**
 * clickhouse 连接管理
 */
class Manager extends DatabaseManager
{
    protected function createPool(array $config): ConnectionPool
    {
        return new ConnectionPool(
            function () use ($config) {
                if (!isset($config['database'])) {
                    throw new RuntimeException('ClickHouse database is required');
                }

                $client = new Client($config);
                $client->database($config['database']);
                $client->setConnectTimeOut($config['connect_timeout'] ?? 5);
                $client->setTimeout($config['timeout'] ?? 5);
                $client->enableHttpCompression();
                if (!$client->ping()) {
                    throw new RuntimeException('ClickHouse connect failed');
                }

                return $client;
            },
            $config['pool_size'] ?? ConnectionPool::DEFAULT_SIZE
        );
    }
}
