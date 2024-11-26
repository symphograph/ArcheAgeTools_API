<?php

namespace App\Prof\Lvl;

use Symphograph\Bicycle\DTO\DTOTrait;

class ProfLvlDTO
{
    use DTOTrait;

    const string tableName = 'profLvls';
    const string colId = 'lvl';

    public int $lvl;
    public int $min;
    public int $max;
    public int $laborBonus;
    public int $timeBonus;

}