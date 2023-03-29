<?php

namespace Library\Database\MySQL;

use Library\ConnectionPool;
use Library\Database\Manager as DatabaseManager;
use Library\Database\PDOConfig;
use Library\Database\PDOPool;
use PDO;

/**
 * MySQL 连接池管理器
 */
class Manager extends DatabaseManager
{
    /**
     * @inheritdoc
     */
    protected function createPool(array $config): ConnectionPool
    {
        $pdoConfig = new PDOConfig();

        // 如果配置了 socket 则使用 socket 连接
        if (isset($config['sock'])) {
            $pdoConfig->withUnixSocket('/tmp/mysql.sock');
        } else {
            $pdoConfig->withHost($config['host']);
            $pdoConfig->withPort($config['port'] ?? 3306);
        }

        $pdoConfig->withDbName($config['database']);
        $pdoConfig->withUsername($config['user']);
        $pdoConfig->withPassword($config['password']);
        $pdoConfig->withCharset($config['charset'] ?? 'utf8mb4');
        $pdoConfig->withOptions([PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);

        return new PDOPool(
            $pdoConfig,
            $config['pool_size'] ?? PDOPool::DEFAULT_SIZE
        );
    }
}
