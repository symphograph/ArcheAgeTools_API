<?php

namespace App\Craft\Craft\Repo;

use App\Craft\Craft\Craft;

interface RepoITF
{
    /**
     * @return Craft[]
     */
    static function getList(int $resultItemId): array;
}