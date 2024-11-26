<?php

namespace App\Item\Repo;

use App\Item\Item;
use PDO;
use Symphograph\Bicycle\PDO\DB;

class RepoDB implements RepoITF
{
    static function getPrivateIds(): array
    {
        $sql = "
            SELECT id FROM items 
            WHERE 
            (
                (
                    !isTradeNPC
                    AND ismat
                    AND !craftable
                    AND onOff
                    AND personal
                )
                OR id IN (SELECT id FROM currency)
            )
            AND id != 500";

        return DB::qwe($sql)->fetchAll(PDO::FETCH_COLUMN) ?? [];
    }

    static function byId(int $id): Item
    {
        $sql = "select * from items where id = :id";
        $params = compact('id');
        return DB::qwe($sql, $params)
            ->fetchObject(Item::class)
            ->initData();
    }
}