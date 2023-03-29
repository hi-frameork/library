<?php

namespace Library\Database\Elasticsearch;

use Elasticsearch\ClientBuilder;
use Library\ConnectionPool;
use Library\Database\Manager as DatabaseManager;

/**
 * ES 链接池管理器
 */
class Manager extends DatabaseManager
{
    /**
     * @inheritdoc
     */
    protected function createPool(array $config): ConnectionPool
    {
        return new ConnectionPool(
            fn () => ClientBuilder::create()
                ->setHosts($config['host'])
                ->setBasicAuthentication($config['username'], $config['password'])
                ->setHandler(new RequestHandler)
                ->build()
            ,
            $config['pool_size'] ?? ConnectionPool::DEFAULT_SIZE
        );
    }
}