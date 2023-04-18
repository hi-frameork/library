<?php

namespace Tests\Unit\Library\Http\Router\Stub\Case1;

use Library\Attribute\Types\Route;

#[Route(prefix: '/test-1', desc: 'test-1')]
class Route1
{
    #[Route(get: '/get', desc: 'test-1-get')]
    public function get()
    {
    }

    #[Route(get: '/middleware', desc: 'test-1-get', middleware: 'middleware1')]
    public function middleware()
    {
    }

    #[Route(get: '/test-cors', desc: 'test-1-get', cors: 'MiddlewareCors')]
    public function cors()
    {
    }
}
