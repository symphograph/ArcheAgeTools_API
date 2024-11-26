<?php

namespace App\Prof\Lvl\Repo;

use App\Prof\Lvl\ProfLvl;
use App\Prof\Lvl\Repo\LvlITF;

class RepoMemory implements LvlITF
{
    static array $lvls = [];

    static function get(int $lvl): ?ProfLvl
    {
        return self::$lvls[$lvl] ?? null;
    }

    static function set(int $lvl, ProfLvl $profLvl): void
    {
        self::$lvls[$lvl] = $profLvl;
    }

    /**
     * @inheritDoc
     */
    static function getList(): array
    {
        return self::$lvls;
    }

    /**
     * @param ProfLvl[] $profLvls
     */
    static function setList(array $profLvls): void
    {
        foreach ($profLvls as $profLvl) {
            self::$lvls[$profLvl->lvl] = $profLvl;
        }
    }

}