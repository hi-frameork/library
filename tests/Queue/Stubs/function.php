<?php

use Library\Queue\AbstractProducer;

function produce(AbstractProducer|string $producer)
{
    $producer->send();
}
