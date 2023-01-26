<?php

namespace App\Transfer;

use PDO;

class ItemList
{
    public static function transferList(int $limit): void
    {
        $List = self::getRandomList($limit);
        foreach ($List as $itemId){
            if(!self::transferItem($itemId)){
                break;
            }
            echo '<hr>';
        }
    }

    private static function getRandomList(int $limit): array
    {
        $qwe = qwe("
            select id from items 
            where onOff
            order by rand()
            limit :limit",
            ['limit' => $limit]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function transferItem(int $itemId): bool
    {
        usleep(500);
        $PageItem = new PageItem($itemId);
        $PageItem->executeTransfer();
        echo "<p>ID: $itemId - {$PageItem->ItemDB->name}</p>";
        //printr($PageItem->ItemDB);
        //printr($PageItem->ItemDTO);
        //echo $PageItem->ItemDTO->description . '<br>';
        if(!empty($PageItem->error)){
            echo $PageItem->error . '<br>';
            echo $PageItem->targetArea;
            return false;
        }
        return true;
    }
}