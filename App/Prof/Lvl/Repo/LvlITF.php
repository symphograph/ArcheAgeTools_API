<?php

namespace App\Prof\Lvl\Repo;

use App\Prof\Lvl\ProfLvl;

interface LvlITF
{
    static function get(int $lvl): ?ProfLvl;

    /**
     * @return ProfLvl[]
     */
    static function getList(): array;
}