<?php

namespace App\DTO;

use Symphograph\Bicycle\DTO\DTOTrait;

class PackDTO
{
    use DTOTrait;
    const tableName = 'packs';

    public int     $itemId;
    public ?int    $zoneFromId;
    public ?string $name;
    public ?string $shortName;
    public ?string $zoneName;
    public ?int    $typeId;
    public ?string $typeName;
    public ?int    $side;
    public ?int    $freshId;
    public ?int    $nativeId;
    public ?int    $doodId;
}