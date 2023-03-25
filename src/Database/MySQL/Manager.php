<?php

namespace Library\Database\MySQL;

use InvalidArgumentException;
use Library\Database\Manager as DatabaseManager;
use Library\Database\PDOConfig;
use Library\Database\PDOPool;
use PDO;

/**
 * 数据库连接池管理器
 */
class Manager extends DatabaseManager
{
    /**
     * 构造函数 - 初始化数据库连接
     */
    public function __construct(protected array $configs)
    {
        foreach ($configs as $name => $config) {
            if (!$config) {
                throw new InvalidArgumentException("Mysql 连接初始化错误: '{$name}' 连接配置为空");
            }

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

            $this->pool[$name] = new PDOPool(
                $pdoConfig,
                $config['pool_size'] ?? PDOPool::DEFAULT_SIZE
            );
        }
    }
}
