<?php

namespace App\Mat;

use Symphograph\Bicycle\DTO\DTOTrait;
use Symphograph\Bicycle\PDO\DB;

class MatDTO
{
    use DTOTrait;
    const string tableName = 'craftMaterials';

    public int            $craftId;
    public int            $itemId;
    public int|float|null $need;
    public int            $matGrade;

    public static function delAllFromCraft(int $craftId): void
    {
        $tableName = self::tableName;
        DB::qwe("
            delete from $tableName 
            where craftId = :craftId 
            and itemId !=500",
            ['craftId'=> $craftId]
        );
    }
}