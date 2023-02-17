<?php

namespace Library\Database;

use Library\Database\Redis\Proxy;

class Redis
{
    /**
     * @var \Redis
     */
    protected $redis;

    protected string $connection = 'default';

    public function __construct()
    {
        $this->redis = new Proxy($this->connection);
    }
}
