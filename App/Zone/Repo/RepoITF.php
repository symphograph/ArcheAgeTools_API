<?php

namespace App\Zone\Repo;

use App\Zone\Zone;

interface RepoITF
{
    static function get(int $id): ?Zone;

    static function all(): array;
}