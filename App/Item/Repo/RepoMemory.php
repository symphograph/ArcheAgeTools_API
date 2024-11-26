<?php

namespace App\Item\Repo;

use App\Item\Item;

class RepoMemory implements RepoITF
{
    private static array $privateIds = [];

    /**
     * @var Item[]
     */
    private static array $items = [];

    static function getPrivateIds(): array
    {
        return self::$privateIds;
    }

    static function setPrivateIds(array $privateIds): void
    {
        self::$privateIds = $privateIds;
    }

    static function byId(int $id): ?Item
    {
        return self::$items[$id] ?? null;
    }

    static function setItem(Item $item): void
    {
        self::$items[$item->id] = $item;
    }
}