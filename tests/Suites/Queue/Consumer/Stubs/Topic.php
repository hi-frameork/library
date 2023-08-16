<?php

namespace Tests\Suites\Queue\Consumer\Stubs;

use Library\Attribute\Queue\TopicDefine;
use Library\Queue\TopicInterface;

enum Topic: string implements TopicInterface
{
    #[TopicDefine(partition: 3)]
    case SinglePartitionSingleConsumer = 'signle_partition_single_consumer';

    #[TopicDefine(partition: 3)]
    case MultiPartitionSignleConsumer = 'multi_partition_single_consumer';

    #[TopicDefine(partition: 3)]
    case SinglePartitionMultiConsumer = 'single_partition_multi_consumer';

    #[TopicDefine(partition: 3)]
    case MultiPartitionMultiConsumer = 'multi_partition_multi_consumer';
}
