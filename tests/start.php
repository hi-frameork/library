<?php

use Library\Coroutine;
use Swoole\Event;

require dirname(__DIR__) . '/vendor/autoload.php';

Coroutine::create(function () {
    require __DIR__ . '/kernel.php';
    PHPUnit\TextUI\Command::main(false);
});

Event::wait();
