<?php

namespace Craft;

class CraftGroup
{
    public ?int    $itemId;
    public ?int    $craftId;
    public ?string $itemName;
    public ?int    $amount;
    public ?int    $groupId;
    public ?int    $sum;

    public static function byCraftId(int $craftId)
    {
        $qwe = qwe("
            select itemName, amount, sum(amount) as sum
            from craftGroups 
            where groupId = 
            (select groupId from craftGroups where craftId = :craftId)",
            ['craftId' => $craftId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }

    }
}