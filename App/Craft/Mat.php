<?php

namespace App\Craft;

use App\DTO\MatDTO;
use App\User\AccSettings;
use App\Item\{Item, Price};
use PDO;

class Mat extends MatDTO
{
    public int    $id;
    public ?int   $resultItemId;
    public ?int   $grade;
    public ?bool  $craftable;
    public ?Item  $Item;
    public ?bool  $isBuyOnly;
    public ?Price $Price;
    public bool $isCounted;


    public static function byIds(int $matId, int $craftId) : self|bool
    {
        $qwe = qwe("
            select i.id,
                   cm.craftId,
                   c.resultItemId,
                   cm.matGrade as grade, 
                   cm.need,
                   i.craftable
            from craftMaterials cm
            inner join items i 
                on cm.itemId = i.id
            inner join crafts c 
                on cm.craftId = c.id
                and c.onOff
            where craftId = :craftId and i.id = :matId",
            ['craftId' => $craftId, 'matId' => $matId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $mat = $qwe->fetchObject(self::class);
        $mat->initItem();
        return $mat;
    }

    private function initIsByOnly(): void
    {
        $this->Item->initIsBuyOnly();
        $this->isBuyOnly = $this->Item->isBuyOnly;
    }

    /**
     * @return int[]
     */
    public static function getCraftMatIDs(int $craftId): array
    {
        $qwe = qwe("
            select itemId
            from craftMaterials
            where craftId = :craftId",
            ['craftId' => $craftId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return self[]|bool
     */
    public static function getCraftMats(int $craftId) : array|bool
    {
        $qwe = qwe("
            select cm.*, 
                   i.id, 
                    matGrade as grade
            from craftMaterials cm 
            inner join items i 
                on cm.itemId = i.id
                and i.onOff
            where craftId = :craftId",
            ['craftId' => $craftId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        /** @var self[] $arr */
        $arr = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
        $List = [];
        foreach ($arr as $mat){
            $mat->initItem();
            $mat->craftable = $mat->Item->craftable;
            $List[] = $mat;
        }

        return $List;
    }

    public function initItem(): void
    {
        $this->Item = Item::byId($this->id);
    }

    /**
     * @return int[]
     */
    public static function allPotentialMats(int $itemId, array $matIDsArr = []): array
    {
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
            select items.id,
                   cm.craftId,
                   crafts.resultItemId,
                   cm.matGrade as grade, 
                   cm.need,
                   items.craftable,
                    if(uCP.itemId, 1, 0) as isCounted
                   from craftMaterials cm
                inner join items 
                    on cm.itemId = items.id
                    and items.onOff
                inner join crafts 
                    on cm.craftId = crafts.id
                    and crafts.onOff
                   left join uacc_CraftPool uCP 
                       on items.id = uCP.itemId
                        and uCP.accountId = :accountId
                        and uCP.serverGroupId = :serverGroupId
                where resultItemId = :itemId
                and uCP.itemId is null",
            [
                'accountId'=> $AccSets->accountId,
                'serverGroupId'=>$AccSets->serverGroupId,
                'itemId'=>$itemId
            ]

        );
        $mats = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
        //printr($mats);
        foreach ($mats as $mat){
            if(in_array($mat->id, $matIDsArr)){
                continue;
            }
            $matIDsArr[] = $mat->id;
            if($mat->craftable){
                $matIDsArr = self::allPotentialMats($mat->id,$matIDsArr);
            }
        }
        return $matIDsArr;
    }

    public function initPrice(): bool
    {
        if($this->id === 500){
            $this->Price = Price::byParams(itemId: 500, price: 1);
            return true;
        }
        self::initIsByOnly();

        if($this->isBuyOnly){
            if(self::initPriceBySaved()){
                return true;
            }

            if(GroupCraft::byCraftId($this->craftId)){
                return self::initPriceByCraft();
            }
            return false;
        }

        if($this->need < 0){
            if(GroupCraft::byCraftId($this->craftId)){
                return self::initPriceByCraft();
            }
            if($this->craftable && self::initPriceByCraft()){
                return true;
            }
            return self::initPriceBySaved();
        }

        if($this->Item->isTradeNPC && !$this->Item->craftable){
            return self::initPriceFromNPC();
        }

        if($this->Item->craftable){
            return self::initPriceByCraft();
        }

        return self::initPriceBySaved();
    }

    private function initPriceBySaved(): bool
    {
        if($Price = Price::bySaved($this->id)){
            $this->Price = $Price;
            return true;
        }

        return false;
    }

    private function initPriceByCraft(): bool
    {
        if($Price = Price::byCraft($this->id)){
            $this->Price = $Price;
            return true;
        }
        if($Price = Price::byBuffer($this->id)){
            $this->Price = $Price;
            return true;
        }
        return false;
    }

    private function initPriceFromNPC(): bool
    {
        if($this->Item->currencyId === 500){
            $this->Price = Price::byParams(
                itemId: $this->id,
                price: $this->Item->priceFromNPC,
                method: 'byFromNPC'
            );
            return true;
        }

        $Price = Price::bySaved($this->id);
        if($Price){
            $this->Price = $Price;
            return true;
        }

        $vPrice = Price::bySaved($this->Item->currencyId);
        if($vPrice){
            $this->Price = Price::byParams(
                itemId: $this->id,
                price: $vPrice->price * $this->Item->priceFromNPC,
                method: 'byFromNPC'
            );
            return true;
        }
        return false;
    }
}