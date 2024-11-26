<?php

namespace App\Prof\Repo;

use App\Prof\Prof;

interface RepITF
{
    static function get(int $id): ?Prof;

    /**
     * @return Prof[]
     */
    static function getList(): array;
}