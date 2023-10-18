<?php

namespace App\Transfer\Items;

use PDO;
use Symphograph\Bicycle\DTO\DTOTrait;

class NewItem
{
    use DTOTrait;
    const tableName = '`NewItems_20230622`';

    public int $id;
    public string $name;
    public int $lvl;

    /**
     * @return self[]|false
     */
    public static function getList(array $orderBy = []): array|false
    {
        $qwe = qwe("select * from " . self::tableName);
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

}