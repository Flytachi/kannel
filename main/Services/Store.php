<?php

namespace Main\Services;


use Flytachi\Kernel\Src\Factory\Connection\Store\RedisStore;

class Store extends RedisStore
{
    protected static string $redisConfigClassName = StoreConfig::class;

    public static function main(): \Redis
    {
        return self::init(env('REDIS_DBNAME', 0));
    }
}
