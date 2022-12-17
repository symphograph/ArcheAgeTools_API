<?php

namespace Item;

class Pricing
{
    private int  $item_id;
    private int  $categ_id;
    public bool  $isTradeNPC;
    public int   $priceFromNPC;
    public int   $priceToNPC;
    public int   $valut_id;
    private bool $personal;
    private bool $craftable;
    public bool  $isGoldable = false;
    public Price $Price;

    public function __set(string $name, $value): void{}

    public static function byItemId(int $item_id): bool|self
    {
        $qwe = qwe("
            select item_id, categ_id, isTradeNPC, priceFromNPC, priceToNPC, valut_id, personal, craftable
            from items 
            where item_id = :item_id",
            ['item_id' => $item_id]
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
        if(in_array($this->categ_id,[133,171,122])){
            return false;
        }

        if ($this->isTradeNPC) {
            if ($this->valut_id == 500)
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
        if($Price = Price::bySolo($this->item_id)){
            $this->Price = $Price;
            return true;
        }
        $this->Price = new Price();
        return true;
    }
}