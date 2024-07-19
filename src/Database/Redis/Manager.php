<?php

namespace Library\Database\Redis;

use Library\ConnectionPool;
use Library\Database\Manager as DatabaseManager;
use Redis;

/**
 * Redis 链接池管理器
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
                $redis = new Redis();
                $redis->pconnect($config['host'], $config['port'] ?? 6379, $config['timeout'] ?? 1);
                $redis->setOption(Redis::OPT_READ_TIMEOUT, -1);

                if (isset($config['password'])) {
                    $redis->auth($config['password']);
                }

                if (isset($config['db'])) {
                    $redis->select($config['db']);
                }

                return $redis;
            },
            $config['pool_size'] ?? ConnectionPool::DEFAULT_SIZE
        );
    }
}
