<?php

namespace App\DTO;

use App\DTO\DTO;
use Symphograph\Bicycle\DTO\DTOTrait;

class DoodDTO extends DTO
{
    use DTOTrait;
    const tableName = 'doods';
    public int    $id;
    public string $name;
}