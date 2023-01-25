<?php

namespace App\Item;

class CurrencyItem
{

    public ?int $currencyPrice;

    public function __construct(public ?Item $Item)
    {
        if (!$this->Item->initPrice()){
            return;
        }
        $this->currencyPrice = ceil($this->Item->Price->price / $this->Item->priceFromNPC);
    }
}