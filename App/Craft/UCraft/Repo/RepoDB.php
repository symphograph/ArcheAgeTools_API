<?php

namespace App\Craft\UCraft\Repo;

use App\Craft\UCraft\UCraft;
use App\User\AccSets;
use Symphograph\Bicycle\PDO\DB;

class RepoDB implements RepoITF
{
    public static function getBest(int $resultItemId): ?UCraft
    {
        $sql = "
            select 
                uc.*,
                if(ubC.craftId, 1, 0) as isUBest
            from uacc_crafts uc
            left join uacc_bestCrafts ubC 
                on uc.craftId = ubC.craftId
                and uc.accountId = ubC.accountId
            where uc.itemId = :itemId 
                and uc.accountId = :accountId
                and serverGroupId = :serverGroupId
            order by isUBest desc, isBest desc, spmu, craftCost
            limit 1";

        $params = [
            'itemId'        => $resultItemId,
            'accountId'     => AccSets::$current->accountId,
            'serverGroupId' => AccSets::$current->serverGroupId,
        ];

        return DB::qwe($sql, $params)->fetchObject(UCraft::class) ?: null;
    }

    public static function byId(int $craftId): ?UCraft
    {
        $sql = "
            select uc.*,
                   if(ubC.craftId, 1, 0) as isUBest
            from uacc_crafts uc
            left join uacc_bestCrafts ubC 
                on uc.accountId = ubC.accountId
                and ubC.craftId = uc.craftId
            where uc.accountId = :accountId
                and serverGroupId = :serverGroupId
                and uc.craftId = :craftId";

        $params = [
            'accountId'     => AccSets::curId(),
            'serverGroupId' => AccSets::curServerGroupId(),
            'craftId'       => $craftId
        ];
        return DB::qwe($sql, $params)->fetchObject(UCraft::class) ?: null;
    }
}