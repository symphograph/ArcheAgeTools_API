<?php

namespace App;
use PDO;

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
    public static function getList(int $itemId): array
    {
        $qwe = qwe("
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
            order by updatedAt desc
            limit 20",
            ['itemId' => $itemId]
        );
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class) ?? [];
    }
}