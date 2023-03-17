<?php

namespace Library\Database\Redis;

use Library\ConnectionPool;
use Library\Database\Manager as DatabaseManager;
use Redis;

class Manager extends DatabaseManager
{
    public function __construct(protected array $configs)
    {
        foreach ($configs as $name => $config) {
            $this->pool[$name] = new ConnectionPool(function () use ($config) {
                $redis = new Redis();
                $redis->connect($config['host'], $config['port'], $config['timeout']);

                if (isset($config['password'])) {
                    $redis->auth($config['password']);
                }

                if (isset($config['db'])) {
                    $redis->select($config['db']);
                }

                return $redis;
            }, $config['pool_size'] ?? 8);
        }

        // debug('初始化 Redis 数据库连接池', $configs);
    }
}
