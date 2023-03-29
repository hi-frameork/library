<?php


// 测试异常 finnally 代码块是否会被执行

use Library\Database\Elasticsearch;

try {
    throw new Exception('test');
} catch (Exception $th) {
    throw $th;
} finally {
    echo 'finally';
    echo PHP_EOL;
}


class A extends Elasticsearch
{
    public function get(array $body, string $id = '')
    {
        $this->index();

        $this->indices();

        $this->search();
    }
}