<?php

namespace Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub;

use Library\Attribute\Types\Middleware;

#[Middleware(alias: 'auth.log', priority: 1)]
class AuthLog
{
    public function handle()
    {
    }
}
