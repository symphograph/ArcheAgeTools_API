<?php

namespace App\Currency\Repo;

class RepoMemory implements RepoITF
{
    /**
     * @var int[][]
     */
    static array $tradeableIds = [];

    /**
     * @var int[]
     */
    static array $ids = [];

    static function getTradeableIds(int $currencyId): array
    {
            return self::$tradeableIds[$currencyId] ?? [];
    }

    static function setTradeableIds(int $currencyId, array $ids): void
    {
        self::$tradeableIds[$currencyId] = $ids;
    }

    static function getIds(): array
    {
        return self::$ids;
    }

    static function setIds(array $ids): void
    {
        self::$ids = $ids;
    }
}