<?php

namespace App\Zone;

use Symphograph\Bicycle\DTO\AbstractList;

class ZoneList extends AbstractList
{
    /**
     * @var Zone[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return Zone::class;
    }

    public static function all(): static
    {
        $sql = "SELECT * FROM zones";
        return static::bySql($sql);
    }

    public static function bySide(int $side): static
    {
        $sql = "SELECT * FROM zones WHERE side = :side";
        $params = compact('side');
        return static::bySql($sql, $params);
    }

    /**
     * @return Zone[]
     */
    public function getList(): array
    {
        return $this->list;
    }
}