<?php

namespace App\Item;

use Symphograph\Bicycle\DTO\AbstractList;

class ItemBaseIconList extends AbstractList
{

    /**
     * @var ItemBaseIcon[]
     */
    protected array $list = [];
    public static function getItemClass(): string
    {
        return ItemBaseIcon::class;
    }

    public static function allOn(int $limit = self::MaxLimit): static
    {
        $params = compact('limit');
        $sql = "select id, icon, iconMD5 from items 
                where onOff 
                order by name, craftable desc, personal, basicGrade
                limit :limit";

        return static::bySql($sql, $params);
    }

    /**
     * @return ItemBaseIcon[]
     */
    public function getList(): array
    {
        return $this->list;
    }
}