<?php

namespace Library\Database\ClickHouse;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\Common\WhereInterface;
use Aura\SqlQuery\QueryInterface;
use ClickHouseDB\Client;
use ClickHouseDB\Statement;
use Library\ConnectionPool;
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
    protected function builtIn(callable $callback)
    {
        /** @var \Library\Database\Manager $manager */
        $manager = app('db.pool.clickhouse');
        /** @var ConnectionPool $pool */
        $pool = $manager->pool($this->connection);
        /** @var Client $client */
        $client = $pool->get();

        try {
            debug('ClickHouse', [str_replace(PHP_EOL, ' ', $this->query->getStatement()), $this->query->getBindValues()]);
            $result = $callback($client);
        } catch (Throwable $th) {
            throw $th;
        } finally {
            $pool->put($client);
        }

        return $result;
    }

    /**
     * 执行 SQL 并返回执行结果集
     * 执行成功，返回 array (结果数据)
     * 执行失败，返回 false
     *
     * @return Statement
     */
    public function execute()
    {
        $sql        = $this->query->getStatement();
        $bindValues = $this->query->getBindValues;

        return $this->builtIn(fn (Client $client) => match (true) {
            ($this->query instanceof SelectInterface) => $client->select($sql, $bindValues),
            ($this->query instanceof DeleteInterface) => $client->write($sql, $bindValues),
            ($this->query instanceof InsertInterface) => $client->write($sql, $bindValues),
            ($this->query instanceof UpdateInterface) => $client->write($sql, $bindValues),
        });
    }
}
