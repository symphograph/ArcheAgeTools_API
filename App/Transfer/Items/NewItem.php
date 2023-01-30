<?php

namespace App\Transfer\Items;

use PDO;

class NewItem
{
    const tableName = 'NewItems_8.0.2.7_9.0.1.6';
    public int $id;
    public string $name;
    public int $lvl;

    /**
     * @return false|array<self>
     */
    public static function getList(string $tableName): false|array
    {
        $qwe = qwe("select * from $tableName");
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }
}