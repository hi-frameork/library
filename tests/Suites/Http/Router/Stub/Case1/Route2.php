<?php

namespace Tests\Unit\Library\Http\Router\Stub\Case1;

use Library\Attribute\Types\Route;

#[Route(prefix: '/test-2', desc: 'test-2', middleware: 'middleware1')]
class Route2
{
    #[Route(get: '/get', desc: 'test-1-get')]
    public function get()
    {
    }
}
