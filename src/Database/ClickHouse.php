<?php

namespace Library\Database;

use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\QueryInterface;
use Library\Database\ClickHouse\QueryProxy;
use RuntimeException;

/**
 * @method InsertInterface|QueryProxy insert()
 * @method InsertInterface|QueryProxy select()
 * @method InsertInterface|QueryProxy update()
 * @method InsertInterface|QueryProxy delete()
 */
class ClickHouse extends AbstractSqlModel
{
    /**
     * 创建 QueryProxy 实例
     *
     * @param QueryInterface $query
     * @return QueryProxy
     */
    protected function createQueryProxy($query)
    {
        return new QueryProxy($this->connection, $query);
    }

    /**
     * 提供原石查询能力
     * callback 回调函数接受一个 \ClickHouseDB\Client 对象参数
     */
    protected function buildIn(callable $callback)
    {
        return $this->select()->builtIn($callback);
    }

    protected function update(?string $table = null)
    {
        throw new RuntimeException('Not allow update in ClickHouse');
    }

    protected function delete(?string $table = null)
    {
        throw new RuntimeException('Not allow delete in ClickHouse');
    }
}
