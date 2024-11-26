<?php

namespace App\Packs;

use Symphograph\Bicycle\DTO\AbstractList;

class PackRouteList extends AbstractList
{
    /**
     * @var PackRoute[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return PackRoute::class;
    }

    public static function all(): static
    {
        $sql = "select * from packRoutes";
        return static::bySql($sql);
    }

    public static function bySide(int $side): static
    {
        $sql = "
            select pr.* from packRoutes pr
            inner join zones z 
                on pr.zoneFromId = z.id
                and z.side = :side
            inner join items
                on pr.itemId = items.id
                and items.onOff";
        $params = ['side' => $side];
        return static::bySql($sql, $params);
    }

    public function initProfit(): static
    {
        foreach ($this->list as $packRoute) {
            $packRoute->initProfit();
        }
        return $this;
    }

    /**
     * @return PackRoute[]
     */
    public function getList(): array
    {
        return $this->list;
    }
}