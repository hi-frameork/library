<?php

namespace Library\Database;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryFactory;
use Library\Database\MySQL\QueryProxy;

abstract class AbstractSqlModel
{
    /**
     * 数据库类型
     */
    private const Type = 'mysql';

    /**
     * 目标数据库连接名称
     */
    protected string $connection = 'default';

    /**
     * 目标数据表
     */
    protected string $table = '';

    /**
     * 返回数据库 Select 对象
     * @see https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/select.md
     *
     * @param array $columns
     * @return SelectInterface|QueryProxy
     */
    protected function select(array $columns = ['*'], ?string $table = null)
    {
        return $this->createQueryProxy(
            (new QueryFactory(self::Type))->newSelect()->from($table ?? $this->table)->cols($columns)
        );
    }

    /**
     * 返回数据库 Update 对象
     * @see https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/update.md
     *
     * @param string $table
     * @return UpdateInterface|QueryProxy
     */
    protected function update(?string $table = null)
    {
        return $this->createQueryProxy(
            (new QueryFactory(self::Type))->newUpdate()->table($table ?? $this->table)
        );
    }

    /**
     * 返回数据库 Insert 对象
     * @see https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/insert.md
     *
     * @param string $table
     * @return InsertInterface|QueryProxy
     */
    protected function insert(?string $table = null)
    {
        return $this->createQueryProxy(
            (new QueryFactory(self::Type))->newInsert()->into($table ?? $this->table)
        );
    }

    /**
     * 返回数据库 Delete 对象
     * @see https://github.com/auraphp/Aura.SqlQuery/blob/3.x/docs/delete.md
     *
     * @param string $table
     * @return DeleteInterface|QueryProxy
     */
    protected function delete(?string $table = null)
    {
        return $this->createQueryProxy(
            (new QueryFactory(self::Type))->newDelete()->from($table ?? $this->table)
        );
    }

    /**
     * 创建 QueryProxy 实例
     */
    abstract protected function createQueryProxy($query);
}
