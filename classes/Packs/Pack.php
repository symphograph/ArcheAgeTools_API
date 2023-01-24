<?php

namespace Packs;

use Craft\AccountCraft;
use Craft\LaborData;
use Item\Item;
use Item\Price;
use PDO;
use User\Account;
use User\Prof;

class Pack
{
    public int     $itemId;
    public ?int    $zoneFromId;
    public ?string $name;
    public ?string $shortName;
    public ?string $zoneName;
    public ?int    $typeId;
    public ?string $typeName;
    public ?int    $side;
    public ?int    $freshId;
    public ?int    $nativeId;
    public ?int    $doodId;
    public string  $icon;
    public int     $grade;
    public int     $passLabor;
    public ?int    $craftPrice;
    public ?int    $laborNeed;

    public const tradeProfId = 5;


    /**
     * @return array<Item>|false
     */
    public static function getPackItems(): array|false
    {
        $qwe = qwe("
            select * from items 
            where onOff 
                and id in 
                (select distinct itemId from packs)"
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, Item::class);

    }

    /**
     * @return array<self>|false
     */
    public static function getList(): array|false
    {
        $qwe = qwe("
            select 
                p.itemId,
                p.zoneFromId,
                i.name,
                z.name as zoneName,
                z.side,
                i.icon,
                if(i.basicGrade, i.basicGrade, 1) as grade,
                p.typeId
            from packs p
            inner join zones z 
                on p.zoneFromId = z.id
            inner join items i
                on p.itemId = i.id
                and i.onOff"
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
    }

    public static function byId(int $itemId, int $zoneFromId)
    {
        $qwe = qwe("
            select 
                p.itemId,
                p.zoneFromId,
                p.freshId,
                i.name,
                z.name as zoneName,
                z.side,
                i.icon,
                if(i.basicGrade, i.basicGrade, 1) as grade,
                p.typeId,
                pT.name as typeName,
                pT.passLabor
            from packs p
            inner join items i
                on p.itemId = i.id
                and i.onOff
                and i.id = :itemId
            inner join zones z 
                on p.zoneFromId = z.id
                and z.id = :zoneFromId
            inner join packTypes pT 
                on p.typeId = pT.id",
            ['itemId'=> $itemId, 'zoneFromId'=>$zoneFromId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public function initCraftData(): bool
    {
        global $Account;
        $CraftData = AccountCraft::byResultItemId($this->itemId);
        self::initPassLabor();
        $this->laborNeed = round($this->passLabor + $CraftData->laborTotal);
        $laborCost = $Account->AccSets->getLaborCost();
        $this->craftPrice = $CraftData->craftCost + $this->passLabor * $laborCost;
        return true;
    }

    private function initPassLabor(): void
    {
        $Prof = Prof::getAccProfById(self::tradeProfId);
        $this->passLabor = LaborData::getBonusedLabor($this->passLabor, $Prof->laborBonus);
    }
}