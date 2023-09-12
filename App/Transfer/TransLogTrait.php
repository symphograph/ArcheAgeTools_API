<?php

namespace App\Transfer;

use PDO;
use Symphograph\Bicycle\DTO\DTOTrait;
use Symphograph\Bicycle\SQL\SQLBuilder;

trait TransLogTrait
{
    use DTOTrait;
    public static function getFilteredErrorList(
        array $filter = [],
        int $startId = 0,
        string $orderBy = 'createdAt',
        ?int $limit = 1000000000
    ): array
    {
        $tableName = self::tableName;
        $filterList = implode(',',$filter);
        $orderBy = self::orderBy($orderBy);
        $qwe = qwe("
            select * from $tableName  
            where error in ($filterList)
            and id >= :startId
            order by $orderBy
            limit :limit",
            ['startId'=> $startId, 'limit' => $limit]
        );
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function getErrorList(int $startId, string $orderBy = 'createdAt', ?int $limit = null): array
    {
        $tableName = self::tableName;
        $orderBy = self::orderBy($orderBy);
        $sql = "
            select * from $tableName 
            where error != ''
            and id >= :startId 
            order by $orderBy
            limit :limit";
        $qwe = qwe($sql, ['startId'=> $startId, 'limit' => $limit ?? 10]);
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function create(int $id, string $name, string $error, array $warnings = []): self
    {
        $Log = new self();
        $Log->id = $id;
        $Log->name = $name;
        $Log->error = $error;
        $Log->initWarnings($warnings);
        $Log->createdAt = date('Y-m-d H:i:s');
        return $Log;
    }
}