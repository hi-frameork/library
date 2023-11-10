<?php

namespace Tests\Unit\Library\Database\MySQL;

use PHPUnit\Framework\TestCase;
use Tests\Asset\MySQLAsset;

class ReadonlyTest extends TestCase
{
    public function testReadonlyException()
    {
        $mysql = new MySQLAsset();
        $mysql->table = 'admin_log';
        $query = $mysql->getInsert();
        $query->cols([
            'id'      => 1,
        ]);
        $query->addRow();
        $query->cols([
            'id'      => 2,
        ]);

        $this->assertSame('2', $query->executeAndGetlastId());
    }
}
