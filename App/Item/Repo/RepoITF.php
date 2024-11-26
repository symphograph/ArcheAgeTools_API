<?php

namespace App\Item\Repo;

use App\Item\Item;

interface RepoITF
{
    static function getPrivateIds(): array;

    static function byId(int $id): ?Item;
}