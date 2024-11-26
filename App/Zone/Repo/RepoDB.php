<?php

namespace App\Zone\Repo;

use App\Zone\Repo\RepoITF;
use App\Zone\Zone;
use App\Zone\ZoneList;
use Symphograph\Bicycle\PDO\DB;

class RepoDB implements RepoITF
{
    static function get(int $id): Zone
    {
        $sql = 'SELECT * FROM zones WHERE id = :id';
        $params = compact('id');
        return DB::qwe($sql, $params)->fetchObject(Zone::class);
    }

    static function all(): array
    {
        return ZoneList::all()->getList();
    }
}