<?php

namespace App\Mat;

use App\User\AccSets;
use Symphograph\Bicycle\DTO\AbstractList;
use Symphograph\Bicycle\PDO\DB;

class MatList extends AbstractList
{
    /**
     * @var Mat[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return Mat::class;
    }

    public static function counted(int $resultItemId): static
    {
        $sql = "
            select items.id,
                   cm.craftId,
                   crafts.resultItemId,
                   cm.matGrade as grade, 
                   cm.need,
                   items.craftable,
                    if(uCP.itemId, 1, 0) as isCounted
                   from craftMaterials cm
                inner join items 
                    on cm.itemId = items.id
                    and items.onOff
                inner join crafts 
                    on cm.craftId = crafts.id
                    and crafts.onOff
                   left join uacc_CraftPool uCP 
                       on items.id = uCP.itemId
                        and uCP.accountId = :accountId
                        and uCP.serverGroupId = :serverGroupId
                where resultItemId = :resultItemId
                and uCP.itemId is null";

        $params = [
            'accountId'     => AccSets::curId(),
            'serverGroupId' => AccSets::curServerGroupId(),
            'resultItemId'  => $resultItemId
        ];

        return static::bySql($sql, $params);
    }

    public static function byCraftId(int $craftId): static
    {
        $sql = "
            select cm.*, 
                   i.id, 
                    matGrade as grade
            from craftMaterials cm 
            inner join items i 
                on cm.itemId = i.id
                and i.onOff
            where craftId = :craftId";
        $params = ['craftId' => $craftId];
        return self::bySql($sql, $params);
    }

    public function initItems(): static
    {
        foreach ($this->list as $mat) {
            $mat->initItem();
        }
        return $this;
    }

    /**
     * @return Mat[]
     */
    public function getList(): array
    {
        return $this->list;
    }
}