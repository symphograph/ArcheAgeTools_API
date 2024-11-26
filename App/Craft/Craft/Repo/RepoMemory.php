<?php

namespace App\Craft\Craft\Repo;

use App\Craft\Craft\Craft;

class RepoMemory implements RepoITF
{
    /**
     * @var Craft[][]
     */
    static array $crafts = [];

    /**
     * @return Craft[]
     */
    static function getList(int $resultItemId): array
    {
        return self::$crafts[$resultItemId] ?? [];
    }

    static function setCrafts(int $resultItemId, array $crafts): void
    {
        self::$crafts[$resultItemId] = $crafts;
    }
}