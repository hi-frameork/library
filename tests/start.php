<?php

use Library\Coroutine;
use Swoole\Event;

require dirname(__DIR__) . '/vendor/autoload.php';

Coroutine::create(function () {
    require dirname(__DIR__) . '/src/kernel.php';
    PHPUnit\TextUI\Command::main(false);
});

Event::wait();
