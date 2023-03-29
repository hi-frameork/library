<?php

namespace Library\Database;

use InvalidArgumentException;
use Library\ConnectionPool;
use RuntimeException;

abstract class Manager
{
    /**
     * @var ConnectionPool[]
     */
    protected array $pool;

    /**
     * 初始化连接池
     */
    public function __construct(protected array $configs)
    {
        foreach ($configs as $name => $config) {
            if (!$config)
                throw new InvalidArgumentException(
                    "Database connection initialization error: '{$name}' connection configuration is empty"
                );

            $this->pool[$name] = $this->createPool($config);
        }
    }

    /**
     * 创建连接池
     */
    abstract protected function createPool(array $config): ConnectionPool;

    /**
     * @return ConnectionPool
     */
    public function pool(string $name)
    {
        // 数据库连接池不许在
        if (!isset($this->pool[$name])) {
            throw new RuntimeException("Database connection pool '{$name}' does not exist");
        }

        return $this->pool[$name];
    }
}
