<?php

namespace Main\Services;

use Flytachi\Kernel\Src\Factory\Connection\Config\RedisConfig;

class StoreConfig extends RedisConfig
{
    public function sepUp(): void
    {
        $this->host = (string) env('REDIS_HOST', '127.0.0.1');
        $this->port = (int) env('REDIS_PORT', 6379);
        $this->password = env('REDIS_PASS', '');
        $this->databaseIndex = env('REDIS_DBNAME', 0);
    }
}
