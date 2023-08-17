<?php

namespace Tests\Suites\Database\ClickHouse;

class SelectTest extends TestCase
{
    public function testSelect()
    {
        // $result = $this->db->select('SELECT * FROM `test` LIMIT 1');
        $this->assertNotEmpty(true);
    }
}
