<?php

/**
 * Copy from https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php
 */

namespace Library;

use Library\Coroutine\Channel;
use RuntimeException;
use Throwable;

class ConnectionPool
{
    public const DEFAULT_SIZE = 64;

    /**
     * @var Channel
     */
    protected $pool;

    /**
     * @var int
     */
    protected $num;

    /**
     * @param callable $constructor
     */
    public function __construct(
        protected $constructor,
        protected $size = self::DEFAULT_SIZE,
        protected ?string $proxy = null
    ) {
        $this->pool = new Channel($size);
        $this->num  = 0;
    }

    public function fill(): void
    {
        while ($this->size > $this->num) {
            $this->make();
        }
    }

    public function get(float $timeout = 2)
    {
        if ($this->pool === null) {
            throw new RuntimeException('Pool has been closed');
        }

        if ($this->pool->isEmpty() && $this->num < $this->size) {
            $this->make();
        }

        $connection = $this->pool->pop($timeout);
        if (!$connection) {
            alert('Connection get timeout', ['timeout' => $timeout]);
        }

        return $connection;
    }

    public function put($connection): void
    {
        if ($this->pool === null) {
            return;
        }

        if ($connection !== null) {
            $this->pool->push($connection);
        } else {
            /* connection broken */
            $this->num -= 1;
            $this->make();
        }
    }

    public function close(): void
    {
        $this->pool->close();
        $this->pool = null;
        $this->num  = 0;
    }

    protected function make(): void
    {
        $this->num++;

        try {
            if ($this->proxy) {
                $connection = new $this->proxy($this->constructor);
            } else {
                $constructor = $this->constructor;
                $connection  = $constructor();
            }
        } catch (Throwable $throwable) {
            $this->num--;

            throw $throwable;
        }

        $this->put($connection);
    }

    public function num()
    {
        return $this->num;
    }

    public function length()
    {
        return $this->pool->length();
    }
}
