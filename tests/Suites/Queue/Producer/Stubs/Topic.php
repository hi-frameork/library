<?php

namespace Tests\Suites\Queue\Producer\Stubs;

use Library\Attribute\Queue\TopicDefine;
use Library\Queue\TopicInterface;

enum Topic: string implements TopicInterface
{
    #[TopicDefine(partition: 3, replicationFactor: 1)]
    case LibraryProducerCreateTopicTest = 'library.producer-create-topic-test';

    #[TopicDefine(partition: 3, replicationFactor: 1)]
    case AttributeProducer100 = 'library.producer-test-100';

    #[TopicDefine(partition: 3, replicationFactor: 1)]
    case AttributeProducer101 = 'library.producer-test-101';

    #[TopicDefine(partition: 3, replicationFactor: 1)]
    case AttributeProducerGroup100 = 'library.producer-test-group-100';

    #[TopicDefine(partition: 3, replicationFactor: 1)]
    case AttributeProducerGroup101 = 'library.producer-test-group-101';
}
