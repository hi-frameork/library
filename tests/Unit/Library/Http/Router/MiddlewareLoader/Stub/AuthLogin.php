<?php

namespace Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub;

use Library\Attribute\Types\Middleware;

#[Middleware(alias: 'auth.login', priority: 5)]
class AuthLogin
{
    public function handle()
    {
    }
}
