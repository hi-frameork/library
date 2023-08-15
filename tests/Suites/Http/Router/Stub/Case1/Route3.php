<?php

namespace Tests\Unit\Library\Http\Router\Stub\Case1;

use Library\Attribute\Types\Route;

#[Route(prefix: '/test-3', desc: 'test-3', cors: 'MiddlewareCors')]
class Route3
{
    #[Route(get: '/get', desc: 'test-3-get')]
    public function get()
    {
    }

    #[Route(post: '/post', desc: 'test-3-post')]
    public function post()
    {
    }
}
