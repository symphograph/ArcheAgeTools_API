<?php

namespace App\Craft\UCraft\Repo;

use App\Craft\UCraft\UCraft;


class RepoMemory implements RepoITF
{
    static array $bests = [];
    static array $crafts = [];

    public static function getBest(int $resultItemId): ?UCraft
    {
        return self::$bests[$resultItemId] ?? null;
    }

    public static function setBest(int $resultItemId, UCraft $best): void
    {
        self::$bests[$resultItemId] = $best;
    }

    static function byId(int $craftId): ?UCraft
    {
        return self::$crafts[$craftId] ?? null;
    }

    static function setCraft(UCraft $craft): void
    {
        self::$crafts[$craft->craftId] = $craft;
    }
}