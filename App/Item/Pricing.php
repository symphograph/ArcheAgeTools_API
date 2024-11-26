<?php

namespace App\Item;

use App\Price\Price;
use App\User\AccSets;

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
        $this->isGoldable = self::isGoldable($this);
    }

    public static function isGoldable(Pricing|Item $item) : bool
    {
        if(Category::isPack($item->categId)){
            return false;
        }

        if ($item->isTradeNPC) {
            if ($item->currencyId == 500)
                return false;

            if ($item->personal)
                return false;
        }

        if($item->personal and $item->craftable){
            return false;
        }

        return true;
    }

    private function initPrice(): void
    {
        $Price = Price::bySaved($this->itemId);
        if(!$Price){
            $this->Price = new Price();
            $this->Price->itemId = $this->itemId;
            $this->Price->author = 'Не найдено';
            return;
        }
        //$Price->initLabel();
        $this->Price = $Price;
    }
}