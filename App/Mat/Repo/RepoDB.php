<?php

namespace App\Mat\Repo;

use App\Mat\Mat;
use App\Mat\MatList;
use App\Mat\MatPool;
use PDO;
use Symphograph\Bicycle\PDO\DB;

class RepoDB implements RepoITF
{

    private const int solidGroupId = 1023;

    /**
     * @return Mat[]
     */
    static function listByCraft(int $craftId): array
    {
        return MatList::byCraftId($craftId)->initItems()->getList();
    }

    static function getSolidIds(): array
    {
        $sql = "
            select id from Categories 
            where parent = :parent";
        $params = ['parent'=> self::solidGroupId];
        return DB::qwe($sql,$params)->fetchAll(PDO::FETCH_COLUMN);
    }
}