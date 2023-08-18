<?php

namespace Tests\Suites\Database\ClickHouse;

use Library\Database\ClickHouse\Manager;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    protected Manager $manager;

    protected function setUp(): void
    {
        $this->manager = new Manager([
            'default' => config('clickhouse.warehouse'),
        ]);
    }
}
