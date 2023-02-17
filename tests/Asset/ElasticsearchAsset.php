<?php

namespace Tests\Asset;

use Library\Database\Elasticsearch;

class ElasticsearchAsset extends Elasticsearch
{
    protected string $index = 'phpunit-test-index';

    public function doGetIndex()
    {
        return $this->getIndex();
    }

    public function doGetClient()
    {
        return $this->client();
    }

    public function doIndex(array $body, string $id = '')
    {
        return $this->index($body, $id);
    }

    public function doGet(string $id, array $body = [], $throw = true)
    {
        return $this->get($id, $body, $throw);
    }

    public function doSearch(array $body)
    {
        return $this->search($body);
    }

    public function doDelete(array $body)
    {
        return $this->delete($body);
    }

    public function doIndices()
    {
        return $this->indices();
    }
}
