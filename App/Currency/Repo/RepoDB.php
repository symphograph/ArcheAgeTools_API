<?php

namespace App\Currency\Repo;

use PDO;
use Symphograph\Bicycle\PDO\DB;

class RepoDB implements RepoITF
{
    public static function getTradeableIds(int $currencyId): array
    {
        $sql = "
            select id 
            from items 
            where currencyId = :currencyId
              and onOff
            and !personal";
        $params = ['currencyId'=> $currencyId];
        $qwe = DB::qwe($sql, $params);

        return $qwe->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public static function getIds(): array
    {
        $sql = "select id from currency";
        return DB::qwe($sql)->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}