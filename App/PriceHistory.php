<?php

namespace App;
use PDO;
use Symphograph\Bicycle\PDO\DB;

class PriceHistory
{
    public int    $accountId;
    public int    $serverGroupId;
    public int    $itemId;
    public int    $price;
    public string $updatedAt;
    public string $itemName;
    public string $publicNick;
    public string $avaFileName;

    /**
     * @return self[]
     */
    public static function getList(int $itemId, int $serverGroupId): array
    {
        $operator = $serverGroupId === 100
            ? '!='
            : '=';
        $sql = "
            select up.*, 
                   items.name as itemName,
                   us.publicNick,
                   us.avaFileName
            from uacc_prices up
            inner join items
                on up.itemId = items.id
            inner join uacc_settings us
                on up.accountId = us.accountId
            where itemId = :itemId
            and up.serverGroupId $operator :serverGroupId
            order by updatedAt desc
            limit 50";
        $params = ['itemId' => $itemId, 'serverGroupId' => $serverGroupId];
        $qwe = DB::qwe($sql,$params);
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class) ?? [];
    }
}