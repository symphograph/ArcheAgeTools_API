<?php

namespace App\Craft\Craft;

use App\Item\Item;
use App\Mat\Mat;
use PDO;
use Symphograph\Bicycle\DTO\AbstractList;
use Symphograph\Bicycle\PDO\DB;

class CraftList extends AbstractList
{
    /**
     * @var Craft[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return Craft::class;
    }

    public static function all(): static
    {
        $sql = "select * from crafts";
        return static::bySql($sql);
    }

    public static function allPotential(int $resultItemId): static
    {
        $craftIDs = Item::getCraftIDs($resultItemId);
        $matIds = Mat::allPotentialIds($resultItemId);
        if (empty($matIds)) $matIds[] = 0;
        $sql = "
            select crafts.*,
                   items.name as itemName,
                   doods.name as  doodName 
            from crafts 
                 inner join items on crafts.resultItemId = items.id
                    and items.onOff
                    and crafts.onOff
                    and (
                            crafts.resultItemId in (:matIds) 
                            or crafts.id in (:craftIDs)
                        )
                 left join doods on doods.id = crafts.doodId
            order by deep desc, resultItemId";
        $params = ['matIds' => $matIds, 'craftIDs' => $craftIDs];
        return static::bySql($sql, $params);
    }

    public static function byResultItemId(int $itemId): static
    {
        $sql = "
            select crafts.*, 
                   doods.id as doodId,
                   doods.name as doodName
            from crafts 
            inner join items on items.id = crafts.resultItemId
                and items.onOff
                and crafts.onOff                   
                and crafts.resultItemId = :itemId
            left join doods 
                on doods.id = crafts.doodId";
        $params = ['itemId' => $itemId];
        return static::bySql($sql, $params);
    }

    /**
     * @param int $itemId
     * @return int[]
     */
    public static function getResultItemIds(int $itemId): array
    {
        $sql = "
        select any_value(crafts.resultItemId) as resultItemId 
        from craftMaterials  
        inner join crafts 
            on craftMaterials.craftId = crafts.id
            and crafts.onOff
        inner join items 
            on items.id = crafts.resultItemId
            and items.onOff
            and craftMaterials.itemId = :itemId
        group by crafts.resultItemId";
        $params = ['itemId' => $itemId];
        return DB::qwe($sql, $params)->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function initMats(): static
    {
        foreach ($this->list as $craft) {
            $craft->initMats();
        }
        return $this;
    }

    public function getList(): array
    {
        return $this->list;
    }

    /**
     * @param string $colName
     * @return Craft[][]
     */
    public function getGrouppedByCol(string $colName): array
    {
        $list = [];
        foreach ($this->list as $craft){
            $list[$craft->$colName][] = $craft;
        }
        return $list;
    }

}