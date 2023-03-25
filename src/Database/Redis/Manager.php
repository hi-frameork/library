<?php

namespace Library\Database\Redis;

use InvalidArgumentException;
use Library\ConnectionPool;
use Library\Database\Manager as DatabaseManager;
use Redis;

class Manager extends DatabaseManager
{
    public function __construct(protected array $configs)
    {
        foreach ($configs as $name => $config) {
            if (!$config) {
                throw new InvalidArgumentException("Redis 连接初始化错误: '{$name}' 连接配置为空");
            }

            $this->pool[$name] = new ConnectionPool(function () use ($config) {
                $redis = new Redis();
                $redis->connect($config['host'], $config['port'], $config['timeout'] ?? 1);

                if (isset($config['password'])) {
                    $redis->auth($config['password']);
                }

                if (isset($config['db'])) {
                    $redis->select($config['db']);
                }

                return $redis;
            }, $config['pool_size'] ?? ConnectionPool::DEFAULT_SIZE);
        }
    }
}
