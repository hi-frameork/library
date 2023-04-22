<?php

namespace Library\Database\Redis;

use function app;

use Library\ConnectionPool;
use PDO;

class Proxy
{
    public function __construct(private string $connection)
    {
    }

    /**
     * 动态代理 redis 方法
     *
     * @param string $name redis 扩展所提供的所有方法名
     * @param array $arguments 方法对应的参数
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->builtIn(
            fn ($redis) => $redis->{$name}(...$arguments)
        );
    }

    /**
     * 以闭包方式执行 redis 操作
     * 传递 redis 连接给闭包作为参数
     * 
     * @param callable $callback
     * @return mixed
     */
    public function builtIn(callable $callback)
    {
        /** @var \Library\Database\Manager $manager */
        $manager = app('db.pool.redis');
        /** @var ConnectionPool $pool */
        $pool = $manager->pool($this->connection);

        /** @var \Redis $redis */
        $redis = $pool->get();

        try {
            $result = $callback($redis);
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            $pool->put($redis);
        }

        return $result;
    }
}
