<?php

namespace Tests\Asset;

use Library\Attribute\Types\Route;

#[Route(prefix: '/api/asset', desc: 'route class', middleware: 'user.inject')]
class RouteAsset
{
    #[Route(post: '/post-path', desc: 'route method post', cors: 'cors.default')]
    public function post(InputParameterAsset $input)
    {
    }

    #[Route(get: '/get-path', desc: 'route method get', middleware: ['middleware.s1', 'middleware.s2'])]
    public function get()
    {
    }

    #[Route(put: '/middleware-string-path', desc: 'route method put', middleware: 'middleware.string')]
    public function middlewareString()
    {
    }

    #[Route(put: '/middleware-array-path', desc: 'route method put', middleware: ['middleware.array1', 'middleware.array2'])]
    public function middlewareArray()
    {
    }
}
