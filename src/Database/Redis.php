<?php

namespace Library\Database;

use Library\Database\Redis\Proxy;

class Redis
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * 数据库连接名称
     */
    protected string $connection = 'default';

    /**
     * Redis Construct
     */
    public function __construct()
    {
        $this->redis = new Proxy($this->connection);
    }
}
