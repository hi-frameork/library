<?php

namespace Tests\Suites\Database\ClickHouse\Actions\Stubs;

use Library\Database\ClickHouse;

class Model extends ClickHouse
{
    protected string $connection = 'default';

    protected string $table = 'test';

    public function doBuiltIn(callable $callback)
    {
        return $this->buildIn($callback);
    }

    public function doSelect()
    {
        return $this->select();
    }

    public function doInsert()
    {
        return $this->insert();
    }

    public function doDelete()
    {
        return $this->delete();
    }

    public function doUpdate()
    {
        return $this->update();
    }
}
