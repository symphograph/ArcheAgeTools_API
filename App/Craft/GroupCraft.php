<?php

namespace App\Craft;

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
        //printr($result);
        return $result;
    }

    public function getMatSum(Craft $craft, $lost = []): MatSum
    {
        $sum = $sumSPM = 0;
        foreach ($craft->Mats as $mat){
            if(!($mat->need > 0)){
                continue;
            }
            if(!$mat->initPrice() && !$mat->Item->craftable){
                //self::addToLost($mat->id);
                $lost[] = $mat->id;
                continue;
            }

            if($Buffer = BufferSecond::byItemId($mat->id)){
                $sumSPM += $Buffer->spm;
            }

            $sum += $mat->Price->price * $mat->need;
        }

        return MatSum::getGroupSum($sum, $this->groupAmount, $sumSPM, $craft, $lost);
    }
}