<?php

namespace App\Mat\Repo;

use App\Mat\Mat;

class RepoMemory implements RepoITF
{
    /**
     * @var Mat[][]
     */
    static array $craftMats = [];
    static array $solidIds = [];

    /**
     * @return Mat[]
     */
    static function listByCraft(int $craftId): array
    {
        return self::$craftMats[$craftId] ?? [];
    }

    static function setList(int $craftId, array $mats): void
    {
        self::$craftMats[$craftId] = $mats;
    }

    static function getSolidIds(): array
    {
        return self::$solidIds;
    }

    static function setSolidIds(array $solidIds): void
    {
        self::$solidIds = $solidIds;
    }
}