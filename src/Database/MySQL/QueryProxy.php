<?php

namespace Library\Database\MySQL;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Mysql\Delete;
use Aura\SqlQuery\Mysql\Insert;
use Aura\SqlQuery\Mysql\Select;
use Aura\SqlQuery\Mysql\Update;
use Aura\SqlQuery\QueryInterface;
use Library\ConnectionPool;
use PDO;
use PDOStatement;
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
     * @param QueryInterface&SelectInterface $query
     */
    public function __construct(protected string $connection, protected QueryInterface $query)
    {
    }

    /**
     * 实现 proxy 目标 $query 方法调用
     *
     * @param string            $name
     * @param array<int,mixed> $arguments
     */
    public function __call($name, $arguments): self
    {
        $this->query->{$name}(...$arguments);

        return $this;
    }

    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * 生成并返回 SQL 语句
     */
    public function getStatement(): string
    {
        return $this->query->getStatement();
    }

    public function getSql(): string
    {
        return implode(' ', array_map('trim', explode(PHP_EOL, $this->query->getStatement())));
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
    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    /**
     * 在 PDO 中运行数据库操作
     *
     * @param callable $callback
     */
    private function runWithPDO(callable $callback): mixed
    {
        /** @var \Library\Database\Manager $manager */
        $manager = app('db.pool.mysql');
        /** @var ConnectionPool $pool */
        $pool = $manager->pool($this->connection);

        /** @var \PDO $pdo */
        $pdo = $pool->get();

        try {
            $sql = $this->getSql();

            debug('MYSQL', [$sql, $this->query->getBindValues()]);

            // SQL 预处理
            $stmt = $pdo->prepare($sql);
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
    public function first(): mixed
    {
        $this->query->limit(1);

        return $this->runWithPDO(
            /**
             * @param PDO $pdo
             * @param PDOStatement $stmt
             */
            fn ($pdo, $stmt): mixed => $stmt->fetch(PDO::FETCH_ASSOC)
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
     * @return array|int|false
     */
    public function execute(): array|int|false
    {
        return $this->runWithPDO(
            /**
             * @param PDO $pdo
             * @param PDOStatement $stmt
             */
            fn ($pdo, $stmt): mixed => match (get_class($this->query)) {
                Insert::class => $stmt->fetchAll(PDO::FETCH_ASSOC),
                Select::class => $stmt->fetchAll(PDO::FETCH_ASSOC),
                Delete::class => $stmt->rowCount(),
                Update::class => $stmt->rowCount(),
            }
        );
    }

    /**
     * 执行 SQL 并返回操作最后一行记录 ID (常用于 insert 操作)
     * 执行成功，返回 string
     * 执行失败，返回 false
     *
     * @return string|false
     */
    public function executeAndGetlastId(): mixed
    {
        return $this->runWithPDO(
            /**
             * @param PDO $pdo
             */
            fn ($pdo) => $pdo->lastInsertId()
        );
    }
}
