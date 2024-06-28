<?php

namespace App\Craft;

use Symphograph\Bicycle\DTO\AbstractList;

class CraftList extends AbstractList
{
    protected array $list = [];
    public static function getItemClass(): string
    {
        return Craft::class;
    }
}