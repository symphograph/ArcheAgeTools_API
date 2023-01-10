<?php

namespace Craft;
use Item\Item;
use Item\Price;
use PDO;

class Mat
{
    public int            $id;
    public ?int           $craftId;
    public ?int           $resultItemId;
    public ?int           $grade;
    public int|float|null $need;
    public ?bool          $craftable;
    public ?Item          $Item;
    public ?bool          $isBuyOnly;
    public ?Price $Price;

    public function __set(string $name, $value): void{}

    public static function byIds(int $matId, int $craftId) : self|bool
    {
        $qwe = qwe("
            select i.id,
                   cm.craftId,
                   cm.resultItemId,
                   cm.matGrade as grade, 
                   cm.need,
                   i.craftable
                   from craftMaterials cm
                inner join items i 
                    on cm.itemId = i.id
                    where craftId = :craftId and id = :matId",
        [ 'craftId'=>$craftId, 'matId'=>$matId]
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
     * @return array<int>
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
     * @return array<self>|bool
     */
    public static function getCraftMats(int $craftId) : array|bool
    {
        $qwe = qwe("
            select *, 
                   itemId as id, 
                   /*if(matGrade, matGrade, 1) as grade */
                    matGrade as grade
            from craftMaterials 
            where craftId = :craftId",
            ['craftId' => $craftId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        /** @var array<self> $arr */
        $arr = $qwe->fetchAll(PDO::FETCH_CLASS, get_class());
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
     * @return array<int>
     */
    public static function allPotentialMats(int $itemId, array $matIDsArr = []): array
    {
        $qwe = qwe("
            select i.id,
                   cm.craftId,
                   cm.resultItemId,
                   cm.matGrade as grade, 
                   cm.need,
                   i.craftable
                   from craftMaterials cm
                inner join items i 
                    on cm.itemId = i.id
                where resultItemId = :itemId",
        ['itemId'=>$itemId]
        );
        $mats = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);

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
        if($this->isBuyOnly || $this->need < 0){
            $Price = Price::bySaved($this->id);
            if($Price){
                $this->Price = $Price;
                return true;
            }
            return false;
        }

        if($this->Item->isTradeNPC && !$this->Item->craftable){
            return self::initPriceFromNPC();
        }

        if($this->Item->craftable){
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

        if($Price = Price::bySaved($this->id)){
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