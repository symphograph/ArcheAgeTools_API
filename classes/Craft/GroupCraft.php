<?php

namespace Craft;

class GroupCraft
{
    public ?string $itemName;
    public ?int $amount;
    public ?int $groupAmount;

    public function __set(string $name, $value): void{}

    public static function byCraftId(int $craftId): self|false
    {
        $qwe = qwe("
            select any_value(items.name) as itemName, 
                   any_value(cg.amount) as amount, 
                   sum(amount) as groupAmount
            from craftGroups cg
            inner join items 
                on cg.itemId = items.id
            where groupId = 
            (select groupId from craftGroups where craftId = :craftId)",
            ['craftId' => $craftId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $result = $qwe->fetchObject(self::class);
        if(!$result->groupAmount){
            return false;
        }
        return $result;
    }
}