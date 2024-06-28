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

    public static function allOn(): static
    {
        $sql = "select * from items 
                where onOff 
                order by name, craftable desc, personal, basicGrade";

        return static::bySql($sql);
    }

    public static function byIds(array $ids): static
    {
        $sql = "select * from items 
                where id in (:ids) 
                and onOff 
                order by name, craftable desc, personal, basicGrade";

        $params = ['ids' => $ids];
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