<?php

namespace Tests\Asset;

use Library\Attribute\Types\Route;

#[Route(prefix: '/api/asset', desc: 'route class')]
class RouteAsset
{
    #[Route(post: '/post-path', desc: 'route method post')]
    public function post(InputParameterAsset $input)
    {
    }

    #[Route(get: '/get-path', desc: 'route method get')]
    public function get()
    {
    }
}
