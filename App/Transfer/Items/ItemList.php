<?php

namespace App\Transfer\Items;


use PDO;

class ItemList
{
    public static function forUpdate(int $startId, string $orderBy, ?int $limit = null): false|array
    {
        $qwe = qwe("
            select id from items 
            where !isLock
                and id >= :startId
            order by $orderBy
            limit :limit",
        ['startId' => $startId, 'limit' => $limit]
        );
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function forAdd(int $startId, string $orderBy, ?int $limit = null): array
    {
        return NewItem::getIdList($startId, $orderBy, $limit);
    }

    public static function errors(int $startId, string $orderBy, ?int $limit = null): array
    {
        $list = ItemTransLog::getErrorList($startId, $orderBy, $limit);
        return array_column($list, 'id');
    }

    public static function errorsFiltered(array $filters, int $startId, string $orderBy, ?int $limit = null): array
    {
        $list = ItemTransLog::getFilteredErrorList($filters, $startId, $orderBy, $limit);
        return array_column($list, 'id');
    }

}