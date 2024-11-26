<?php

namespace App\Prof;

use Symphograph\Bicycle\DTO\AbstractList;

class ProfList extends AbstractList
{
    /**
     * @var Prof[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return Prof::class;
    }

    public static function all(): static
    {
        $sql = "SELECT * FROM profs";
        return static::bySql($sql);
    }

    /**
     * @return Prof[]
     */
    public function getList(): array
    {
        return $this->list;
    }
}