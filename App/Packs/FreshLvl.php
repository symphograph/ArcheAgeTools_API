<?php

namespace App\Packs;

class FreshLvl
{
    const conditions = [
        1 => [
            'новый',
            'свежий',
            'подержанный',
            'поврежденный',
            'недоукомплектованный'
        ],
        2 => [
            'новый',
            'выдержанный',
            'подержанный',
            'испорченный',
            'недоукомплектованный'
        ],
        3 => [
            'новый',
            'подержанный',
            'поврежденный',
            'испорченный'
        ],
        4 => ['не портится']
    ];

    public int    $condType;
    public int    $lvl;
    public int    $percent;
    public string $name;


    public static function byConstruct(int $condType, int $lvl, int $percent): self
    {
        $Lvl = new self();
        $Lvl->condType = $condType;
        $Lvl->lvl = $lvl;
        $Lvl->percent =$percent;
        $Lvl->initName();
        return $Lvl;
    }

    public static function getList(array $percents)
    {

    }

    private function initName(): void
    {
        //printr($this->condType);
        $this->name = self::conditions[$this->condType][$this->lvl];
    }
}