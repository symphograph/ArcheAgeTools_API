<?php

namespace App\DTO;

use Symphograph\Bicycle\DTO\DTOTrait;

class DoodDTO
{
    use DTOTrait;
    const tableName = 'doods';

    public int    $id;
    public string $name;
}