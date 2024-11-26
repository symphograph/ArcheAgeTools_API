<?php

namespace App\Prof\Lvl\Repo;

use App\Prof\Lvl\ProfLvl;
use App\Prof\Lvl\Repo\LvlITF;

class ProfLvlRepo implements LvlITF
{

    static function get(int $lvl): ProfLvl
    {
        $profLvl = RepoMemory::get($lvl);
        if(!empty($profLvl)) return $profLvl;

        $profLvls = RepoDB::getList();
        RepoMemory::setList($profLvls);

        return RepoMemory::get($lvl);
    }


    /**
     * @return ProfLvl[]
     */
    static function getList(): array
    {
        $profLvls = RepoMemory::getList();
        if(!empty($profLvls)) return $profLvls;

        $profLvls = RepoDB::getList();
        RepoMemory::setList($profLvls);
        return RepoMemory::getList();
    }
}