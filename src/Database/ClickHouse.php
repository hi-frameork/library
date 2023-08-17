<?php

namespace Library\Database;

use Aura\SqlQuery\QueryInterface;
use Library\Database\ClickHouse\QueryProxy;

class ClickHouse extends Model
{
    /**
     * 创建 QueryProxy 实例
     *
     * @param QueryInterface $query
     */
    protected function createQueryProxy($query): QueryProxy
    {
        return new QueryProxy($this->connection, $query);
    }
}
