<?php

namespace App\Craft\UCraft\Repo;

use App\Craft\UCraft\UCraft;

interface RepoITF
{
    static function getBest(int $resultItemId): ?UCraft;

    static function byId(int $craftId): ?UCraft;
}