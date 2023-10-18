<?php

namespace App\DTO;


use PDO;
use Symphograph\Bicycle\DTO\DTOTrait;

class ProfDTO
{
    use DTOTrait;
    const tableName = 'profs';

    public int $id;
    public string $name;
    public int $used;

    /**
     * @return self[]
     */
    public static function listByName(string $name): array
    {
        $tableName = self::tableName;
        $qwe = qwe("select * from $tableName where name = :name", ['name' => $name]);
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }
}