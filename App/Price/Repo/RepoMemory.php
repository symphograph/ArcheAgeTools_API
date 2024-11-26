<?php

namespace App\Price\Repo;

use App\Price\Price;
use App\Price\Repo\RepoITF;

class RepoMemory implements RepoITF
{
    /**
     * @var Price[]
     */
    static array $prices = [];

    public static function byAccount(int $itemId, int $accountId, int $serverGroup): ?Price
    {
        return self::$prices[$itemId] ?? null;
    }

    public static function set(Price $price): void
    {
        self::$prices[$price->itemId] = $price;
    }

    public static function get(int $itemId): ?Price
    {
        return self::$prices[$itemId] ?? null;
    }
}