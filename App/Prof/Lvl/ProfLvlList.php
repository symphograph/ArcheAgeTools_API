<?php

namespace App\Prof\Lvl;

use Symphograph\Bicycle\DTO\AbstractList;

class ProfLvlList extends AbstractList
{
    /**
     * @var ProfLvl[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return ProfLvl::class;
    }

    public static function all(): static
    {
        $sql = "SELECT * FROM profLvls";
        return static::bySql($sql);
    }

    public function initLabel(): static
    {
        foreach ($this->list as $profLvl) {
            $profLvl->initLabel();
        }
        return $this;
    }

    /**
     * @return ProfLvl[]
     */
    public function getList(): array
    {
        return $this->list;
    }
}