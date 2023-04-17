<?php

namespace Database\MySQL;

class Transaction
{
    public static function begin()
    {
        // $db = Database::getInstance();
        // $db->query('START TRANSACTION');
    }

    public static function commit()
    {
        // $db = Database::getInstance();
        // $db->query('COMMIT');
    }

    public static function rollback()
    {
        // $db = Database::getInstance();
        // $db->query('ROLLBACK');
    }

    public static function run(callable $callback)
    {
        // self::begin();
        // try {
        //     $callback();
        //     self::commit();
        // } catch (\Exception $e) {
        //     self::rollback();
        //     throw $e;
        // }
    }
}