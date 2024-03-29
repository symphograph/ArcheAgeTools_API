<?php

namespace App\Packs;

use App\User\AccSettings;
use PDO;

class PackIds
{
    /**
     * @return array<int>
     */
    public static function getAll(): array
    {
        $qwe = qwe("
            select distinct p.itemId 
            from packs p
            inner join items i 
                on i.id = p.itemId
                and i.onOff"
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<int>
     */
    public static function getUncounted(int $side = 1): array
    {
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
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
            where itemId is null",
            ['side'=>$side, 'accountId'=> $AccSets->accountId, 'serverGroupId' => $AccSets->serverGroupId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }
}