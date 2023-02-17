<?php

namespace Library\Database;

use Swoole\Database\PDOConfig as SwPdoConfig;

/**
 * @method PdoConfig withHost(string $host)
 * @method PdoConfig withPort(string $host)
 * @method PdoConfig withDbName(string $host)
 * @method PdoConfig withCharset(string $host)
 * @method PdoConfig withUsername(string $host)
 * @method PdoConfig withPassword(string $host)
 */
class PdoConfig extends SwPdoConfig
{
}
