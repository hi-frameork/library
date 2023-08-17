<?php

namespace Library\Database;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;
use Library\Database\MySQL\QueryProxy;

class Model
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
     * 只读数据库连接名称
     */
    protected string $readConnection = 'default';

    /**
     * 使用读库查询
     */
    public function useRead(): self
    {
        $this->connection = $this->readConnection;

        return $this;
    }

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
     * @return SelectInterface|QueryProxy
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
     * @return SelectInterface|QueryProxy
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
     * @return SelectInterface|QueryProxy
     */
    protected function delete(?string $table = null)
    {
        return $this->createQueryProxy(
            (new QueryFactory(self::Type))->newDelete()->from($table ?? $this->table)
        );
    }

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
     * 获取键名为 ID 对应的记录数据
     */
    public function firstById(int $id, array $columns = ['*'])
    {
        return $this->select($columns)->where('id = :a', ['a' => $id])->first();
    }

    /**
     * 返回指定 ID 集记录列表
     */
    public function findByIds(array $ids, array $columns = ['*'])
    {
        return $this->select($columns)
            ->where('id IN (:a)', ['a' => $ids])
            ->execute()
        ;
    }

    /**
     * 新增一条记录，并返回记录 ID
     *
     * @param bool $batch 是否批量插入，如果单行插入返回最后一条记录 ID
     */
    public function create(array $data, bool $batch = false)
    {
        if ($batch) {
            return $this->insert()->addRows($data)->execute();
        }

        return $this->insert()->cols($data)->executeAndGetlastId();
    }

    /**
     * 检查指定 ID 记录是否存在
     */
    public function existById(int $id)
    {
        $result = $this->select(['id'])
            ->where('id = :a', ['a' => $id])
            ->first()
        ;

        return $result ? true : false;
    }

    /**
     * 更新指定 ID 记录对应数据
     */
    public function updateById(int $id, array $data)
    {
        return $this->update()
            ->cols($data)
            ->where('id = :a', [
                'a' => $id
            ])
            ->execute()
        ;
    }

    /**
     * 根据 ID 遍历表所有数据，每次遍历 $count 条
     *
     * @param int      $count    每次遍历条数
     * @param callable $callback 回调函数
     * @param array    $column   遍历字段
     */
    public function chunkById(int $count, callable $callback, $column = ['*']): void
    {
        $id = 0;

        do {
            $result = $this->select($column)
                ->where('id > :a', ['a' => $id])
                ->orderBy(['id ASC'])
                ->limit($count)
                ->execute()
            ;

            if ($result) {
                $callback($result);
                $id = $result[count($result) - 1]['id'];
            }
        } while ($result);
    }
}
