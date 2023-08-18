<?php

namespace Tests\Suites\Database\ClickHouse\Actions;

use RuntimeException;
use Tests\Suites\Database\ClickHouse\TestCase;

class UpdateTest extends TestCase
{
    public function testUpdateWillThrowException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not allow update in ClickHouse');
        (new Stubs\Model())->doUpdate();
    }
}
