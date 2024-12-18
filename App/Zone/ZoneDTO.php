<?php

namespace App\Zone;

use Symphograph\Bicycle\DTO\DTOTrait;

class ZoneDTO
{
    use DTOTrait;
    const tableName = 'zones';

    public int    $id;
    public string $name;
    public ?int   $side;
    public ?int   $isGet;
    public ?int   $getWest;
    public ?int   $getEast;
    public ?int   $freshTypeOld;
}