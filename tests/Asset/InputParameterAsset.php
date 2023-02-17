<?php

namespace Tests\Asset;

use Library\Attribute\Types\Http;

class InputParameterAsset
{
    #[Http(source: 'post.id', desc: 'ID', rule: 'ranger:1-9')]
    public int $id;

    #[Http(source: 'post.name', desc: '名称')]
    public string $name;
}
