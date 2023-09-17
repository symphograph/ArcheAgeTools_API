<?php

namespace App\Craft;

use App\AppStorage;
use App\DTO\CraftDTO;
use App\User\AccSettings;
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
    public ?int $deep;

    public function __set(string $name, $value): void
    {
    }

    public static function clearStorage(): void
    {
        AppStorage::getSelf()->CraftsFirst = [];
    }

    public static function clearDB(): void
    {
        $AccSets = AccSettings::byGlobal();
        qwe("
            delete from craftBuffer 
                   where accountId = :accountId",
            ['accountId' => $AccSets->accountId]
        );
    }

    public static function putToDB(int $craftId, int $craftCost, int $matSPM): void
    {
        $AccSets = AccSettings::byGlobal();
        qwe("
            replace into craftBuffer 
                (accountId, craftId, craftCost, matSPM) 
            VALUES 
                (:accountId, :craftId, :craftCost, :matSPM)", [
                'accountId' => $AccSets->accountId,
                'craftId'   => $craftId,
                'craftCost' => $craftCost,
                'matSPM'    => $matSPM
            ]
        );
    }

    public static function putToStorage(Craft $craft, int $craftCost, int $matSPM): void
    {
        $bufferFirst = new self();
        $bufferFirst->craftId = $craft->id;
        $bufferFirst->craftCost = $craftCost;
        $bufferFirst->matSPM = $matSPM;
        $bufferFirst->spm = $craft->spm;
        $bufferFirst->resultItemId = $craft->resultItemId;
        $bufferFirst->deep = $craft->deep;
        $bufferFirst->isUBest = Craft::getUBest($craft->resultItemId) === $craft->id;
        $bufferFirst->initSPMU();
        AppStorage::getSelf()->CraftsFirst[] = $bufferFirst;
    }

    private function initSPMU(): void
    {
        $kry = $this->getKRY();
        $spmu = sqrt($kry);
        $this->spmu = round($spmu);
    }



    private function getKRY(): int
    {
        $buyOnlyItems = AppStorage::getSelf()->buyOnlyItems;
        if(in_array($this->resultItemId,$buyOnlyItems)){
            return $this->craftCost;
        }
        $spm = $this->spm - $this->matSPM;
        $kry = $spm + $this->matSPM;
        $kry = sqrt($kry);
        $kry = round($kry);
        $kry *= $this->craftCost;
        $kry += $this->craftCost;
        return abs($kry);
    }


    /**
     * @return array<self>|false
     */
    public static function getCounted(int $resultItemId): array|false
    {
        /*
        $AccSets = AccSettings::byGlobal();
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
        ['resultItemId' => $resultItemId, 'accountId' => $AccSets->accountId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }

        $list = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
        */
        $list2 = AppStorage::getSelf()->CraftsFirst;
        self::clearStorage();
        //printr(array_column($list, 'craftId'));
        //printr(array_column($list2, 'craftId'));

        return $list2;
    }
}