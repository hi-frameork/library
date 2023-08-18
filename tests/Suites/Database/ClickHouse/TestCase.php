<?php

namespace Tests\Suites\Database\ClickHouse;

use Library\Database\ClickHouse\Manager;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

require_once __DIR__ . '/Stubs/function.php';

abstract class TestCase extends FrameworkTestCase
{
    protected string $connection = 'default';

    protected Manager $manager;

    protected function setUp(): void
    {
        $manager = new Manager([
            $this->connection => config('clickhouse.warehouse'),
        ]);

        kernel()->getContainer()->set('db.pool.clickhouse', $manager);

        $this->manager = $manager;
    }
}
