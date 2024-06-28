<?php

namespace App\Transfer\Items;

use PDO;
use Symphograph\Bicycle\DTO\DTOTrait;

class NewItem
{
    use DTOTrait;
    const string tableName = 'NewItems_20231220';

    public int $id;
    public string $name;
    public int $lvl;
    public string $createdAt;

    /**
     * @return self[]|false
     */
    public static function getList(array $orderBy = []): array|false
    {
        $qwe = qwe("select * from " . self::tableName);
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function byImportedArr(array $item): self
    {
        $self = new self();
        $self->id = $item[0];
        $self->name = strip_tags($item[2]);
        $self->lvl = $item[3];

        return $self;
    }

}