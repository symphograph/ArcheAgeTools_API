<?php

namespace App\Prof\Repo;

use App\Prof\Prof;
use App\Prof\Repo\RepITF;

class RepoMemory implements RepITF
{
    static array $profs = [];

    static function get(int $id): ?Prof
    {
        return self::$profs[$id] ?? null;
    }

    static function set(Prof $prof): void
    {
        self::$profs[$prof->id] = $prof;
    }


    /**
     * @return Prof[]
     */
    static function getList(): array
    {
        return self::$profs;
    }

    static function setList(array $profs): void
    {
        foreach ($profs as $prof) {
            self::set($prof);
        }
    }
}