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
     * 连接池释放锁
     */
    protected bool $gcLock = false;

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

    public function get(float $timeout = 1)
    {
        if ($this->pool === null) {
            throw new RuntimeException('Pool has been closed');
        }

        if ($this->pool->isEmpty() && $this->num < $this->size) {
            $this->make();
        } else {
            $this->gc();
        }

        $connection = $this->pool->pop($timeout);
        if (!$connection) {
            alert(sprintf('Timeout getting connection object from [%s] pool', $this->name), [
                'timeout' => $timeout,
                'length'  => $this->pool->length(),
                'num'     => $this->num,
                'size'    => $this->size,
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
        if ($this->gcLock) {
            return;
        }

        // 避协程切换导致的并发问题
        $this->gcLock = true;

        $time = time();
        if ($time - $this->lastGCTime < self::GC_INTERVAL) {
            return;
        }

        // 基于当前 chan 中剩余连接数，释放多余的连接
        // 否则将会出现已分配的连接都在使用中，可用的连接被释放后业务反而获取不到连接的情况
        // 每次释放 2 个连接
        if ($this->pool->length() - $this->minObjectNum >= self::GC_COUNT) {
            for ($i = 0; $i < self::GC_COUNT; $i++) {
                if ($connection = $this->pool->pop(0)) {
                    unset($connection);
                    $this->num--;
                    info("连接释放 [{$this->name}] 连接池剩余连接数: " . $this->num, [
                        $this->name,
                        $this->minObjectNum,
                        $this->num,
                        $this->pool->length(),
                    ]);
                }
            }

            // 记录最后一次释放时间
            $this->lastGCTime = $time;
        }

        $this->gcLock = false;
    }
}
