<?php

namespace App\DTO;

use Symphograph\Bicycle\DTO\DTOTrait;

class ZoneDTO extends DTO
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