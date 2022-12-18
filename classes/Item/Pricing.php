<?php

namespace Item;

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
        if(in_array($this->categId,[133, 171, 122])){
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

    private function initPrice(): bool
    {
        if($Price = Price::bySolo($this->itemId)){
            $this->Price = $Price;
            return true;
        }
        $this->Price = new Price();
        return true;
    }
}