<?php

namespace Tests\Asset;

use Library\Database\Model;

/**
 * MySQL 桩
 */
class MySQLAsset extends Model
{
    protected string $table = 'test';

    public function getSelect()
    {
        return $this->select();
    }

    public function getInsert()
    {
        return $this->insert();
    }

    public function getDelete()
    {
        return $this->delete();
    }

    public function getUpdate()
    {
        return $this->update();
    }
}
