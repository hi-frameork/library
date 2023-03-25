<?php

use Hi\Http\Application;
use Library\Database\MySQL\Manager;

require __DIR__ . '/../vendor/autoload.php';

$dbPool = new Manager([
    'test' => [
        'host' => '192.168.64.4',
        'port' => 3306,
        'database' => 'test-connect',
        'user' => 'root',
        'password' => '123456',
        'pool_size' => 1,
    ]
]);

$app = new Application('swoole', [
    'swoole' => [
        'log_file' => '/tmp/swoole.log',
        'pid_file' => '/tmp/swoole.pid',
    ]
]);
$app->get('/db-reconnect-test', function () use ($dbPool) {

    try {
        $pool = $dbPool->pool('test');

        // print_r($pool);
        /** @var \PDO $pdo */
        $pdo = $pool->get();
        // SQL 预处理
        $stmt = $pdo->prepare('select * from NewTable where id > ?');
        // SQL 语句参数绑定并执行
        $stmt->execute([0]);
    } catch (\Throwable $e) {
    $pool->put($pdo);
        return json_encode([
            'type' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'stack' => $e->getTraceAsString(),
        ]);
    }

    $pool->put($pdo);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return json_encode($result);
});
$app->listen(80, '0.0.0.0');
