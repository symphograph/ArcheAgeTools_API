<?php

namespace App\Transfer\Items;

use App\DTO\DTO;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use PDO;
use Symphograph\Bicycle\DTO\DTOTrait;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Helpers;

class NewItem extends DTO
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