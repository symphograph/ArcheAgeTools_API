<?php

namespace App\Item;

use Symphograph\Bicycle\DTO\DTOTrait;
use Symphograph\Bicycle\Errors\AppErr;

class ItemBaseIcon
{
    use DTOTrait;

    public int     $id;
    public ?string $icon;
    public ?string $iconMD5;

    public function beforePut(): void
    {
        throw new AppErr('this class can`t put to db');
    }
}