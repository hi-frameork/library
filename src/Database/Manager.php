<?php

namespace Library\Database;

use Library\ConnectionPool;

class Manager
{
    /**
     * @var ConnectionPool[]
     */
    protected array $pool;

    /**
     * @return ConnectionPool
     */
    public function pool(string $name)
    {
        return $this->pool[$name];
    }
}
