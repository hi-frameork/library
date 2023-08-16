<?php

namespace Tests\Suites\Queue\Producer\Stubs;

use Library\Attribute\Queue\Producer;
use Library\Queue\AbstractProducer;
use Library\Queue\TopicInterface;

#[Producer(alias: 'producer-test-101')]
class AttributeProducer101 extends AbstractProducer
{
    protected string $connection = 'kafka-default';

    protected TopicInterface $topic = Topic::AttributeProducer101;

    public function __construct(
        protected ?array $data = null,
        protected bool $batch = false,
    ) {
        parent::__construct($data, $batch);
        $this->config->setAutoCreateTopic(true);
    }
}
