<?php

namespace App\Item;

use Symphograph\Bicycle\DTO\AbstractList;

class ItemList extends AbstractList
{
    /**
     * @var Item[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return Item::class;
    }

    public static function allOn(int $limit = self::MaxLimit): static
    {
        $sql = "select *, if(basicGrade,basicGrade,1) as grade from items 
                where onOff 
                order by name, craftable desc, personal, basicGrade
                limit :limit";
        $params = ['limit' => $limit];

        return static::bySql($sql, $params);
    }

    public static function byIds(array $ids): static
    {
        $sql = "
            select *, 
            if(basicGrade,basicGrade,1) as grade 
            from items 
            where onOff 
              and id in (:ids) 
            order by name, craftable desc, personal, basicGrade";

        $params = ['ids' => $ids];
        return static::bySql($sql, $params);
    }

    public static function all(
        ?string $orderBy = null,
        ?int    $limit = null,
        ?int    $minId = null,
        ?int    $maxId = null
    ): static
    {
        $sql = "select * from items 
         where id between :minId and :maxId";
        $sql = static::sql($sql, $orderBy, $limit);
        if (empty($maxId)) {
            $maxId = static::getMaxId();
        }
        $params = ['minId' => $minId, 'maxId' => $maxId];
        return static::bySql($sql, $params);
    }

    public static function emptyIcons(
        ?string $orderBy = null,
        ?int    $limit = null,
        ?int    $minId = null,
        ?int    $maxId = null
    )
    {
        $sql = "select * from items where iconId is null and id between :minId and :maxId";
        $sql = static::sql($sql, $orderBy, $limit);
        if (empty($maxId)) {
            $minId = 0;
        }
        if (empty($maxId)) {
            $maxId = static::getMaxId();
        }

        $params = ['minId' => $minId, 'maxId' => $maxId];
        return static::bySql($sql, $params);
    }

    /**
     * @return Item[]
     */
    public function getList(): array
    {
        return $this->list;
    }
}