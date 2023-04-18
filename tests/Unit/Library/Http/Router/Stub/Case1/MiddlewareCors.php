<?php

namespace Tests\Unit\Library\Http\Router\Stub\Case1;

use Library\Attribute\Types\Middleware;

#[Middleware(alias: 'MiddlewareCors')]
class MiddlewareCors
{
}