<?php

use Library\Coroutine;

require __DIR__ . '/../../vendor/autoload.php';

Coroutine::create(function () {
    $client = \Elasticsearch\ClientBuilder::create()
        ->setHosts(['192.168.64.2:30000'])
        ->setBasicAuthentication('elastic', 'J2yf8wGK700YH3325QSA5kik')
        ->setHandler(new \Library\Database\Elasticsearch\RequestHandler)
        // ->setHandler(\Elasticsearch\ConnectionPool\Selectors\RoundRobinSelector::class)
        ->build();

    // 创建一个 ES 记录
    $response = $client->index([
        'index' => 'my_index',
        'id' => 'my_id',
        'body' => ['testField' => 'abc']
    ]);
    print_r($response);

    // 查询一个 ES 记录
    $response = $client->get([
        'index' => 'my_index',
        'id' => 'my_id',
    ]);
    print_r($response);

    // 更新一个 ES 记录
    $response = $client->update([
        'index' => 'my_index',
        'id' => 'my_id',
        'body' => [
            'doc' => ['testField' => 'xyz']
        ]
    ]);
    print_r($response);

    // 删除一个 ES 记录
    $response = $client->delete([
        'index' => 'my_index',
        'id' => 'my_id',
    ]);

    print_r($response);

    // Info API
    $response = $client->info();
    print_r($response);

    // echo $response['version']['number'];
    // var_dump($response);

});
