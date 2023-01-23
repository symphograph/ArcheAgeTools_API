<?php

namespace Packs;

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

    public string $name;
    public function __construct(
        public int $condType,
        public int $lvl,
        public int $percent
    )
    {
        $this->name = self::findName();
    }

    private function findName(): string
    {
        return self::conditions[$this->condType][$this->lvl];
    }
}