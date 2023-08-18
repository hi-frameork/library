<?php

namespace Tests\Suites\Database\ClickHouse\Actions;

use RuntimeException;
use Tests\Suites\Database\ClickHouse\TestCase;

class DeleteTest extends TestCase
{
    public function testDeleteWillThrowException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not allow delete in ClickHouse');
        (new Stubs\Model())->doDelete();
    }
}
