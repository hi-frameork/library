<?php

return (new \Hi\Kernel($_ENV['APP_PATH'] ?? dirname(__DIR__)))->load(function (\Hi\Kernel\Container $container) {
    /*
    |--------------------------------------------------------------------------
    | Kernel 基础配置
    |--------------------------------------------------------------------------
    */
    $container->get('config')->merge(
        \Symfony\Component\Yaml\Yaml::parseFile(basePath('/tests/application.yaml'))
    );

    // $container->set('db.elasticsearch', fn () => new \Library\Database\Elasticsearch\Manager([
    //     'default' => [
    //         'host' => [
    //             '192.168.64.4:30000'
    //         ],
    //         'username' => 'elastic',
    //         'password' => 'J2yf8wGK700YH3325QSA5kik',
    //     ]
    // ]));

    /*
    |--------------------------------------------------------------------------
    | 消息队列配置
    |--------------------------------------------------------------------------
    */
    // $container->set('queue', fn () => new \Library\Queue\Manager([
    //         'kafka-default' => config('queue.kafka-default'),
    //     ], [
    //         basePath('/src/server/Queue'),
    //     ])
    // );
});
