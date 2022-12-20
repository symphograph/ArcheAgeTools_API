<?php

namespace Test;

use Api;
use Item\{Item, Price};
use User\Account;

class Test
{
    public static function ItemList()
    {

    }

    public static function pricingByItemId(): void
    {
        $List = Item::searchList() or die(Api::errorMsg('pricingByItemId err'));
        foreach ($List as $item){
            if (!($Pricing = \Item\Pricing::byItemId($item->id))) {
                echo "<br>item_id: $item->id. err";
            }
        }
    }

    public static function PriceFinder(): void
    {
        $Account = Account::bySess();
        $Account->initMember();
        $List = Item::searchList();
        foreach ($List as $item){
            $Price = Price::getPrice($item->id,1);
            if(!$Price) continue;
            $Price->initLabel();
            echo $item->name . '<br>';
            printr($Price);
            echo '<hr>';
        }
    }
}