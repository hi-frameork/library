<?php

namespace Library\Database;

use BadMethodCallException;
use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Exception;

/**
 * @method IndicesNamespace indices(string $id) 获取指定 ID 的文档
 * @method string index(array $body, string $id = '') 创建 Index && Doc
 * @method array get(string $id, array $body = [], bool $throwException = true) 获取指定 Doc
 * @method bool delete(array $body) 删除指定 ID 的文档
 * @method array search(array $body) 搜索，返回值 array{total: array, hits: array}
 * @method array bulk(array $data) 批量创建
 */
abstract class Elasticsearch
{
    /**
     * 连接名称
     */
    protected string $connection = 'default';

    /**
     * 索引名称
     */
    protected string $index = '';

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
     * @return IndicesNamespace
     */
    private function indices(): callable
    {
        return fn (Client $client) => $client->indices();
    }

    /**
     * 创建 Index && Doc
     *
     * @return string 文档插入 ID
     */
    private function index(array $body, string $id = ''): callable
    {
        $params = [
            'index' => $this->getIndex(),
            'body'  => $body,
        ];

        if ($id) {
            $params['id'] = $id;
        }

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
         *
         * 取 ID 字段值
         */
        return fn (Client $client) => $client->index($params)['_id'] ?? '';
    }

    /**
     * 获取指定 Doc
     */
    private function get(string $id, array $body = [], bool $throwException = true)
    {
        return function (Client $client) use ($id, $body, $throwException) {
            try {
                $result = $client->get([
                    'index' => $this->getIndex(),
                    'id'    => $id,
                    ...$body,
                ]);

                return $result['_source'];
            } catch (Exception $th) {
                $throwException && throw $th;
            }

            return null;
        };
    }

    /**
     * 搜索
     *
     * @return array{total: array, hits: array}
     */
    private function search(array $body, array $option = []): callable
    {
        return function (Client $client) use ($body, $option) {
            $result = $client->search([
                'index' => $this->getIndex(),
                'body'  => $body,
            ]);

            // 同时返回打分
            if (!empty($option['with_score'])) {
                foreach ($result['hits']['hits'] as &$value) {
                    $value['_source']['_score'] = $value['_score'];
                }
            }

            $hits    = $result['hits'];
            $sources = array_column($hits['hits'], '_source');

            return [
                'total' => $hits['total'],
                'hits'  => $sources,
            ];
        };
    }

    /**
     * 删除指定文档
     */
    private function delete(array $body, bool $throwException = true): callable
    {
        return function (Client $client) use ($body, $throwException) {
            try {
                $result = $client->delete([
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
        };
    }

    /**
     * 批量操作更新
     */
    private function bulk(array $data)
    {
        return fn (Client $client) => $client->bulk([
            'index' => $this->getIndex(),
            'body'  => $data,
        ]);
    }

    /**
     * 需要动态调用的方法需要在此手动添加
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this, $name)) {
            throw new BadMethodCallException(
                sprintf("Method %s::%s does not exist.", static::class, $name)
            );
        }

        return $this->run(
            $this->{$name}(...$arguments)
        );
    }

    /**
     * @param callable $callback
     * @return Client
     */
    protected function run(callable $callback)
    {
        /** @var \Library\Database\Manager $manager */
        $manager = app('db.elasticsearch');
        /** @var \Library\ConnectionPool $pool */
        $pool = $manager->pool($this->connection);

        /** @var Client $client */
        $client = $pool->get();

        try {
            // tips: 不能使用 call_user_func，因为 call_user_func 会将闭包转换为字符串，导致内存泄漏
            $result = $callback($client);
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            $pool->put($client);
        }

        return $result;
    }
}
