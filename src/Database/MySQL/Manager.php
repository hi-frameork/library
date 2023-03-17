<?php

namespace Library\Database\MySQL;

use Library\Database\Manager as DatabaseManager;
use Library\Database\PdoConfig;
use Library\Database\PdoPool;

class Manager extends DatabaseManager
{
    public function __construct(protected array $configs)
    {
        foreach ($configs as $name => $config) {
            if (!$config) {
                error("Mysql 连接初始化错误: '{$name}' 连接配置错误, ", [$config]);
            }

            $pdoConfig = new PdoConfig();
            $pdoConfig->withHost($config['host']);
            $pdoConfig->withPort($config['port'] ?? 3306);
            // $pdoConfig->withUnixSocket('/tmp/mysql.sock')
            $pdoConfig->withDbName($config['database']);
            $pdoConfig->withUsername($config['user']);
            $pdoConfig->withPassword($config['password']);
            $pdoConfig->withCharset($config['charset'] ?? 'utf8mb4');

            $this->pool[$name] = new PdoPool($pdoConfig, $config['pool_size'] ?? 8);

            // $this->pool[$name] = new ConnectionPool(
            //     fn () => new PDO(
            //         sprintf(
            //             "mysql:host=%s;port=%s;dbname=%s",
            //             $config['host'],
            //             $config['port'],
            //             $config['database']
            //         ),
            //         $config['user'],
            //         $config['password'],
            //         [
            //             \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
            //         ]
            //     ),
            //     $config['pool_size']
            // );
        }

        // debug('初始化 MySQL 数据库连接池', $configs);
    }
}
