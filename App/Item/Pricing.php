<?php

namespace App\Item;

use App\User\AccSettings;

class Pricing
{
    private int  $itemId;
    private int  $categId;
    private bool $personal;
    private bool $craftable;
    public int   $priceFromNPC;
    public int   $priceToNPC;
    public int   $currencyId;
    public bool  $isTradeNPC;
    public bool  $isGoldable = false;
    public Price $Price;

    public function __set(string $name, $value): void{}

    public static function byItemId(int $itemId): bool|self
    {
        $qwe = qwe("
            select id as itemId, categId, isTradeNPC, priceFromNPC, priceToNPC, currencyId, personal, craftable
            from items 
            where id = :itemId",
            ['itemId' => $itemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Pricing = $qwe->fetchObject(self::class);
        $Pricing->initGoldable();
        $Pricing->initPrice();
        return $Pricing;
    }

    public function initGoldable(): void
    {
        $this->isGoldable = self::isGoldable();
    }

    private function isGoldable() : bool
    {
        if(Category::isPack($this->itemId)){
            return false;
        }

        if ($this->isTradeNPC) {
            if ($this->currencyId == 500)
                return false;

            if ($this->personal)
                return false;
        }

        if($this->personal and $this->craftable){
            return false;
        }

        return true;
    }

    private function initPrice(): void
    {
        $AccSets = AccSettings::byGlobal();
        $Price = Price::bySaved($this->itemId);
        if(!$Price){
            $this->Price = new Price();
            $this->Price->itemId = $this->itemId;
            $this->Price->author = 'Не найдено';
            return;
        }
        $Price->initLabel();
        $this->Price = $Price;
    }
}