<?php

namespace Item;

use PDO;

class Pack
{
    /**
     * @return array<Item>|false
     */
    public static function getPackItems(): array
    {
        $qwe = qwe("
            select * from items 
            where onOff 
                and id in 
                (select distinct item_id from packs)"
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, Item::class);

    }

    /**
     * @return array<int>
     */
    public static function getPackIds(): array
    {
        $qwe = qwe("
            select distinct items.id from items
            inner join packs p 
                on items.id = p.item_id
            where onOff"
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }
}