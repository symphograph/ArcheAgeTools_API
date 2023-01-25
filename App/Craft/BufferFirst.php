<?php

namespace App\Craft;

use PDO;

class BufferFirst
{
    public int $accountId;
    public int $craftId;
    public int $craftCost;
    public int $matSPM;

    public ?int $resultItemId;
    public ?string $itemName;
    public ?int $categId;
    public ?int $resultAmount;
    public ?string $doodName;
    public ?bool $isUBest;
    public ?int $spm;
    public ?int $spmu;
    public ?int $kry;

    public function __set(string $name, $value): void
    {
    }

    public static function clearDB(): void
    {
        global $Account;
        qwe("
            delete from craftBuffer 
                   where accountId = :accountId",
            ['accountId' => $Account->id]
        );
    }

    public static function putToDB(int $craftId, int $craftCost, int $matSPM): bool
    {
        global $Account;
        $qwe = qwe("
            replace into craftBuffer 
                (accountId, craftId, craftCost, matSPM) 
            VALUES 
                (:accountId, :craftId, :craftCost, :matSPM)", [
                'accountId' => $Account->id,
                'craftId'   => $craftId,
                'craftCost' => $craftCost,
                'matSPM'    => $matSPM
            ]
        );
        return boolval($qwe);
    }

    /**
     * @return array<self>|false
     */
    public static function getCounted(int $resultItemId): array|false
    {
        global $Account;
        $qwe = qwe("
            select  * , 
                    ROUND(if(tmp.kry>0,SQRT(tmp.kry),SQRT(tmp.kry*-1)*-1)) as spmu
            from(
                select 
                    items.id as resultItemId,
                    items.name as itemName,
                    items.categId,
                    crafts.id as craftId,
                    doods.name as doodName,
                    crafts.resultAmount,
                    cb.craftCost,
                    ((crafts.spm+cb.matSPM)*(if(bo.itemId,0,1))) as spm,
                    ROUND(SQRT((crafts.spm+cb.matSPM)))*(if(bo.itemId,0,1))*cb.craftCost+cb.craftCost as kry,
                    crafts.deep,
                    if(ubC.craftId, 1, 0) as isUBest,
                    cb.matSPM
                from crafts
                inner join craftBuffer cb
                    on cb.craftId = crafts.id
                    and crafts.resultItemId = :resultItemId
                    and cb.accountId = :accountId
                inner join items 
                    on items.id = crafts.resultItemId
                left join uacc_bestCrafts ubC 
                    on ubC.accountId = cb.accountId
                    and ubC.craftId = cb.craftId
                left join uacc_buyOnly bo
                    on bo.accountId = cb.accountId
                    and bo.itemId = items.id
                left join doods
                    on doods.id = crafts.doodId
            ) as tmp
            order by isUBest desc , deep desc , resultItemId, spmu, craftCost, resultAmount desc",
        ['resultItemId' => $resultItemId, 'accountId' => $Account->id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }
}