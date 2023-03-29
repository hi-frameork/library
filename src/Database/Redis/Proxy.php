<?php

namespace Library\Database\Redis;

use function app;

use Library\ConnectionPool;

class Proxy
{
    public function __construct(private string $connection)
    {
    }

    public function __call($name, $arguments)
    {
        /** @var \Library\Database\Manager $manager */
        $manager = app('db.pool.redis');
        /** @var ConnectionPool $pool */
        $pool = $manager->pool($this->connection);

        /** @var \Redis $redis */
        $redis = $pool->get();

        try {
            debug('REDIS', [$name, $arguments]);

            $result = $redis->{$name}(...$arguments);
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            $pool->put($redis);
        }

        return $result;
    }
}
