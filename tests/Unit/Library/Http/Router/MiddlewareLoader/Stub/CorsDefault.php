<?php

namespace Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub;

use Library\Attribute\Types\Middleware;

#[Middleware(alias: 'cors.default', priority: 0)]
class CorsDefault
{
    public function handle()
    {
    }
}
