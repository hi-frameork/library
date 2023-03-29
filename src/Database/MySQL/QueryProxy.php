<?php

namespace Library\Database\MySQL;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\WhereInterface;
use Aura\SqlQuery\QueryInterface;
use Library\ConnectionPool;
use PDO;
use Throwable;

/**
 * SQL 构建器代理器
 * 为 select, update, insert, delete 等 sql 构建做统一代理
 *
 * 基于连接池技术，由于 db 连接源宝贵只能在 DB 真正执行 SQL 才从连接池获取连接
 * 同时保证连接在 SQL 执行结束之后(正常或异常)可以被正常被连接池回收
 */
class QueryProxy
{
    /**
     * QueryProxy Construct
     *
     * @param string                         $connection 目标数据库
     * @param SelectInterface|WhereInterface $query
     */
    public function __construct(protected string $connection, protected QueryInterface $query)
    {
    }

    /**
     * 实现 proxy 目标 $query 方法调用
     */
    public function __call($name, $arguments)
    {
        $this->query->{$name}(...$arguments);

        return $this;
    }

    /**
     * 生成并返回 SQL 语句
     */
    public function getStatement(): string
    {
        return $this->query->getStatement();
    }

    /**
     * 返回 SQL 语句对应的绑定参数
     */
    public function getBindValues(): array
    {
        return $this->query->getBindValues();
    }

    /**
     * 返回 $query 对象
     *
     * @return QueryInterface
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * 在 PDO 中运行数据库操作
     */
    private function runWithPDO(callable $callback)
    {
        /** @var \Library\Database\Manager $manager */
        $manager = app('db.pool.mysql');
        /** @var ConnectionPool $pool */
        $pool = $manager->pool($this->connection);

        /** @var \PDO $pdo */
        $pdo = $pool->get();

        try {
            debug('MYSQL', [str_replace(PHP_EOL, ' ', $this->query->getStatement()), $this->query->getBindValues()]);

            // SQL 预处理
            $stmt = $pdo->prepare($this->query->getStatement());
            // SQL 语句参数绑定并执行
            $stmt->execute($this->query->getBindValues());
            // 执行回调（个性化业务，例如：获取 lastInsertId, 首行数据）
            $result = $callback($pdo, $stmt);
        } catch (Throwable $th) {
            throw $th;
        } finally {
            $pool->put($pdo);
        }

        return $result;
    }

    /**
     * 执行 SQL 并返回执行第一条结果
     * 执行成功，返回 array
     * 执行失败或者记录未找到，返回 false
     *
     * @return array|false
     */
    public function first()
    {
        $this->query->limit(1);

        return $this->runWithPDO(
            fn ($pdo, $stmt) => $stmt->fetch(PDO::FETCH_ASSOC)
        );
    }

    /**
     * 执行 SQL 并返回执行第一条结果
     * 执行成功，返回 int
     * 执行失败，返回 null
     */
    public function count(string $key = 'total'): ?int
    {
        $result = $this->first();

        return $result[$key] ?? null;
    }

    /**
     * 执行 SQL 并返回执行结果集
     * 执行成功，返回 array (结果数据)
     * 执行失败，返回 false
     *
     * @return array[]|false
     */
    public function execute()
    {
        return $this->runWithPDO(
            fn ($pdo, $stmt) => $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * 执行 SQL 并返回操作最后一行记录 ID (常用于 insert 操作)
     * 执行成功，返回 string
     * 执行失败，返回 false
     *
     * @return string|false
     */
    public function executeAndGetlastId()
    {
        return $this->runWithPDO(
            fn ($pdo) => $pdo->lastInsertId()
        );
    }
}
