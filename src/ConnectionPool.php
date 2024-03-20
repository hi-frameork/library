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
     * 定时器循环检查间隔时间
     * 单位/秒
     */
    public const GC_INTERVAL = 60;

    /**
     * 每次释放连接数
     */
    public const GC_COUNT = 2;

    /**
     * 最小连接数
     */
    protected int $minObjectNum = 4;

    /**
     * 上一次释放时间
     */
    protected int $lastGCTime = 0;

    /**
     * @var Channel
     */
    protected $pool;

    /**
     * @var int
     */
    protected $num;

    protected $name = '';

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

        // Coroutine::create(fn () => $this->gc());
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

        $this->gc();

        if ($this->pool->isEmpty() && $this->num < $this->size) {
            $this->make();
        }

        $every = $timeout / 10;
        for ($i = 0; $i < 10; $i++) {
            $connection = $this->pool->pop($every);
            if (!$connection) {
                info("Timeout getting connection object from [{$this->name}] pool");
                continue;
            }
            break;
        }

        if (!$connection) {
            alert(sprintf('Timeout getting connection object from [%s] pool', $this->name), [
                'connection' => $connection,
                'timeout'    => $timeout,
                'length'     => $this->pool->length(),
                'num'        => $this->num,
                'size'       => $this->size,
            ]);
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setMinObjectNum(int $minObjectNum): self
    {
        $this->minObjectNum = $minObjectNum;

        return $this;
    }

    public function getMinObjectNum(): int
    {
        return $this->minObjectNum;
    }

    /**
     * 回收空闲连接
     */
    protected function gc()
    {
        $time = time();
        if ($time - $this->lastGCTime < self::GC_INTERVAL) {
            return;
        }

        $this->lastGCTime = $time + 1;

        // 基于当前 chan 中剩余连接数，释放多余的连接
        // 否则将会出现已分配的连接都在使用中，可用的连接被释放后业务反而获取不到连接的情况
        // 每次释放 2 个连接
        for ($i = 0; $i < self::GC_COUNT; $i++) {
            if ($this->pool->length() - $this->minObjectNum > 0) {
                if ($connection = $this->pool->pop(0)) {
                    unset($connection);
                    $this->num--;
                    info("连接释放 [{$this->name}] 连接池剩余连接数: " . $this->num, [
                        'name'         => $this->name,
                        'minObjectNum' => $this->minObjectNum,
                        'num'          => $this->num,
                        'length'       => $this->pool->length(),
                    ]);
                }
            }
        }
    }
}
