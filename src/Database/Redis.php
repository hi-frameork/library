<?php

namespace Library\Database;

use Library\Database\Redis\Proxy;

class Redis
{
    /**
     * @var \Redis|Proxy
     */
    protected $redis;

    /**
     * 数据库连接名称
     */
    protected string $connection = 'default';

    /**
     * 失败重试次数
     */
    private int $failedRetries = 3;

    /**
     * Redis Construct
     */
    public function __construct()
    {
        $this->redis = new Proxy($this->connection, $this->failedRetries);
    }
}
