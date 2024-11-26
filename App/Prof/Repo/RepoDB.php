<?php

namespace App\Prof\Repo;

use App\Prof\Prof;
use App\Prof\ProfList;
use Symphograph\Bicycle\PDO\DB;

class RepoDB implements RepITF
{

    static function get(int $id): ?Prof
    {
        $sql = "SELECT * FROM profs WHERE id = :id";
        $params = compact("id");
        return DB::qwe($sql, $params)->fetchObject(Prof::class) ?: null;
    }

    /**
     * @return Prof[]
     */
    static function getList(): array
    {
        return ProfList::all()->getList();
    }
}