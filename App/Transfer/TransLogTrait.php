<?php

namespace App\Transfer;

use PDO;
use Symphograph\Bicycle\DTO\DTOTrait;
use Symphograph\Bicycle\PDO\DB;
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
        //$filterList = implode(',',$filter);
        //printr($filterList);
        $orderBy = self::orderBy($orderBy);
        $qwe = DB::qwe("
            select * from $tableName  
            where error in (:filter)
            and id >= :startId
            order by $orderBy
            limit :limit",
            ['filter' => $filter, 'startId'=> $startId, 'limit' => $limit]
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


}