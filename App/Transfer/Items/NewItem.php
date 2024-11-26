<?php

namespace App\Transfer\Items;

use PDO;
use Symphograph\Bicycle\DTO\DTOTrait;

class NewItem
{
    use DTOTrait;

    const string tableName = 'NewItems';

    public int    $id;
    public string $name;
    public int    $lvl;
    public string $createdAt;

    public static function byImportedArr(array $item): self
    {
        $self = new self();
        $self->id = $item[0];
        $self->name = strip_tags($item[2]);
        $self->lvl = $item[3];

        return $self;
    }

}