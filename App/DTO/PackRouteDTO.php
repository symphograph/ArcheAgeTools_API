<?php

namespace App\DTO;


use Symphograph\Bicycle\DTO\DTOTrait;

class PackRouteDTO
{
    use DTOTrait;
    const tableName = 'packRoutes';

    public int  $id;
    public int  $itemId;
    public int  $zoneFromId;
    public int  $zoneToId;
    public ?int  $dbPrice;
    public int  $currencyId;
    public int $mul;
}