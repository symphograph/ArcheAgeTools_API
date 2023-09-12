<?php

namespace App\DTO;

use App\DTO\DTO;
use Symphograph\Bicycle\DTO\DTOTrait;

class MatDTO extends DTO
{
    use DTOTrait;
    const tableName = 'craftMaterials';

    public int            $craftId;
    public int            $itemId;
    public int|float|null $need;
    public int            $matGrade;

    public static function delAllFromCraft(int $craftId): void
    {
        $tableName = self::tableName;
        qwe("
            delete from $tableName 
            where craftId = :craftId 
            and itemId !=500",
            ['craftId'=> $craftId]
        );
    }
}