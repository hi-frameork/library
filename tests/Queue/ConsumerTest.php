<?php

namespace Tests\Queue;

use PHPUnit\Framework\TestCase;

class ConsumerTest extends TestCase
{
    public function testConsume(): void
    {
        // 1. Arrange
        $this->mockConsumerRunner->expects($this->once())
            ->method('consume')
            ->with(null);
        // 2. Act
        $this->mockConsumer->consume();
        // 3. Assert
    }

    public function testConsumeWithAlias(): void
    {
        // 1. Arrange
        $this->mockConsumerRunner->expects($this->once())
            ->method('consume')
            ->with('user_online_status');
        // 2. Act
        $this->mockConsumer->consume('user_online_status');
        // 3. Assert
    }
}
