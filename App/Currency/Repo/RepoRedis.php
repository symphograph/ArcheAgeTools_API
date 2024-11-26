<?php

namespace App\Currency\Repo;

use Redis;

class RepoRedis implements RepoITF
{
    protected static Redis $redis;

    public static function init(Redis $redis): void
    {
        self::$redis = $redis;
    }

    static function getTradeableIds(int $currencyId): array
    {
        $key = self::buildKey($currencyId);
        $data = self::$redis->get($key);

        return $data ? json_decode($data, true) : [];
    }

    static function setTradeableIds(int $currencyId, array $ids): void
    {
        $key = self::buildKey($currencyId);
        self::$redis->set($key, json_encode($ids), 3600); // Кэш на час
    }

    private static function buildKey(int $currencyId): string
    {
        return "currency:tradeable_ids:$currencyId";
    }
}
