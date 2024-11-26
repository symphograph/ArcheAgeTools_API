<?php

namespace App\Prof\Lvl\Repo;

use App\Prof\Lvl\ProfLvl;
use App\Prof\Lvl\ProfLvlList;
use App\Prof\Lvl\Repo\LvlITF;
use Symphograph\Bicycle\PDO\DB;

class RepoDB implements LvlITF
{

    static function get(int $lvl): ?ProfLvl
    {
        $sql = 'SELECT * FROM profLvls WHERE lvl = :lvl';
        $params = compact('lvl');
        return DB::qwe($sql, $params)->fetchObject(ProfLvl::class) ?: null;
    }

    /**
     * @return ProfLvl[]
     */
    static function getList(): array
    {
        return ProfLvlList::all()->getList();
    }
}