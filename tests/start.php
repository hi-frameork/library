<?php

use Library\Coroutine;
use PHPUnit\TextUI\Command;
use Swoole\Event;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/kernel.php';

define('AppDebug', true);

Coroutine::create(
    fn () => Command::main(false)
);

Event::wait();
