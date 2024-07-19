<?php

namespace Library\Database\Redis;

use function app;

use Library\ConnectionPool;

class Proxy
{
    public function __construct(private string $connection, private int $failedRetries = 3)
    {
    }

    /**
     * @param array<string,mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        /** @var ConnectionPool $pool */
        $pool = app('db.pool.redis')->pool($this->connection);

        $hasError = false;
        $throw = null;
        for ($i = 0; $i < $this->failedRetries; $i++) {
            /** @var \Redis $redis */
            $redis = $pool->get();
            try {
                debug('REDIS', [$name, $arguments]);
                return $redis->{$name}(...$arguments);
            } catch (\Throwable $th) {
                $hasError = true;
                $throw = $th;
            } finally {
                if ($hasError === false) {
                    $pool->put($redis);
                }
            }
        }

        throw $throw;
    }
}
