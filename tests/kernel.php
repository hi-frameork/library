<?php


return (new \Hi\Kernel($_ENV['APP_PATH'] ?? dirname(__DIR__)))->load(function (\Hi\Kernel\Container $container) {
    $container->set('db.elasticsearch', fn () => new \Library\Database\Elasticsearch\Manager([
        'default' => [
            'host' => [
                '192.168.64.4:30000'
            ],
            'username' => 'elastic',
            'password' => 'J2yf8wGK700YH3325QSA5kik',
        ]
    ]));
});
