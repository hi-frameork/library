<?php

namespace Library\Database;

use Library\ConnectionPool;
use PDO;

/**
 * https://github.com/swoole/library/blob/4.8.x/src/core/Database/PDOPool.php
 */
class PDOPool extends ConnectionPool
{
    public function __construct(
        protected PDOConfig $config,
        int $size = self::DEFAULT_SIZE
    ) {
        parent::__construct(fn () => new PDO(
            $config->getDSN(),
            $config->getUsername(),
            $config->getPassword(),
            $config->getOptions()
        ), $size, PDOProxy::class);
    }
}
