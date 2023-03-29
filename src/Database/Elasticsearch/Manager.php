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
            function () use ($config) {
                $builder = ClientBuilder::create()
                    ->setHosts($config['host'])
                    ->setHandler(new RequestHandler)
                ;
                if (isset($config['username']) && isset($config['password'])) {
                    $builder->setBasicAuthentication($config['username'], $config['password']);
                }

                return $builder->build();
            },
            $config['pool_size'] ?? ConnectionPool::DEFAULT_SIZE
        );
    }
}
