<?php

namespace App\Packs;

use App\User\AccSets;
use PDO;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\PDO\DB;

class PackIds
{
    /**
     * @return int[]
     */
    public static function getAll(): array
    {
        $sql = "
            select distinct p.itemId 
            from packs p
            inner join items i 
                on i.id = p.itemId
                and i.onOff";

        return DB::qwe($sql)
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return int[]
     * @throws AccountErr
     */
    public static function getUncounted(int $side = 1): array
    {
        $sql = "
            select tmp.id 
            from 
            (select 
                    distinct i.id,
                    uC.itemId                
                from items i
                inner join packs p 
                    on i.id = p.itemId
                    and i.onOff
                inner join zones z 
                    on p.zoneFromId = z.id
                    and z.side = :side
                left join uacc_crafts uC 
                    on i.id = uC.itemId
                    and uC.accountId = :accountId
                    and uC.serverGroupId = :serverGroupId
                    and isBest
            ) as tmp
            where itemId is null";

        $params = [
            'side'          => $side,
            'accountId'     => AccSets::curId(),
            'serverGroupId' => AccSets::curServerGroupId()];

        return DB::qwe($sql, $params)
            ->fetchAll(PDO::FETCH_COLUMN);

    }
}