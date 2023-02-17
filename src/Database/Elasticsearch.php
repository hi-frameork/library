<?php

namespace Library\Database;

use Exception;

abstract class Elasticsearch
{
    protected string $index = '';

    /**
     * @return \Elasticsearch\Client
     */
    protected function client()
    {
        return app('db.elasticsearch');
    }

    /**
     * 设置索引名称
     */
    protected function setIndex(string $index): void
    {
        $this->index = $index;
    }

    /**
     * 返回索引名称
     */
    protected function getIndex(): string
    {
        return $this->index;
    }

    /**
     * 创建 Index && Doc
     *
     * @return string 文档插入 ID
     */
    protected function index(array $body, string $id = ''): string
    {
        $params = [
            'index' => $this->getIndex(),
            'body'  => $body,
        ];

        if ($id) {
            $params['id'] = $id;
        }

        $result = $this->client()->index($params);
        /**
         * $result = Array
         *     (
         *         [_index] => voya_user_tag_male
         *         [_type] => _doc
         *         [_id] => 123456
         *         [_version] => 9
         *         [result] => updated
         *         [_shards] => Array
         *             (
         *                 [total] => 2
         *                 [successful] => 1
         *                 [failed] => 0
         *             )
         *         [_seq_no] => 8
         *         [_primary_term] => 1
         *     )
         */

        return $result['_id'] ?? '';
    }

    /**
     * @return \Elasticsearch\Namespaces\IndicesNamespace
     */
    protected function indices()
    {
        return $this->client()->indices();
    }

    /**
     * 获取指定 Doc
     */
    protected function get(string $id, array $body = [], bool $throwException = true): ?array
    {
        try {
            $result = $this->client()->get([
                'index' => $this->getIndex(),
                'id'    => $id,
                ...$body,
            ]);

            return $result['_source'];
        } catch (Exception $th) {
            $throwException && throw $th;
        }

        return null;
    }

    /**
     * 搜索
     *
     * @return array{total: array, hits: array}
     */
    protected function search(array $body)
    {
        $result = $this->client()->search([
            'index' => $this->getIndex(),
            'body'  => $body,
        ]);
        $hits    = $result['hits'];
        $sources = array_column($hits['hits'], '_source');

        return [
            'total' => $hits['total'],
            'hits'  => $sources,
        ];
    }

    /**
     * 删除指定文档
     */
    protected function delete(array $body, bool $throwException = true): bool
    {
        try {
            $result = $this->client()->delete([
                'index' => $this->getIndex(),
                ...$body,
            ]);
            /**
             * $result = Array
             *     (
             *         [_index] => voya_user_tag_male
             *         [_type] => _doc
             *         [_id] => 123456
             *         [_version] => 15
             *         [result] => deleted
             *         [_shards] => Array
             *             (
             *                 [total] => 2
             *                 [successful] => 1
             *                 [failed] => 0
             *             )
             *         [_seq_no] => 14
             *         [_primary_term] => 1
             *     )
             */

            return $result['result'] === 'deleted';
        } catch (Exception $th) {
            $throwException && throw $th;
        }

        return false;
    }
}
