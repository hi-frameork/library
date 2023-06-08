<?php

namespace Tests\Queue\Stubs;

use Library\Queue\TopicInterface;

enum Topic: string implements TopicInterface
{
    // 用户上线状态
    case UserOnlineStatus = 'user_online_status';

    case UserOnlineStatusB = 'user_online_status_b';
}
