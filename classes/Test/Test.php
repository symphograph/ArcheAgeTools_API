<?php

namespace Test;

use Api;
use Item\Item;

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
}