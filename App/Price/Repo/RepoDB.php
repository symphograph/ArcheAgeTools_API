<?php

namespace App\Price\Repo;

use App\Price\Method;
use App\Price\Price;
use Symphograph\Bicycle\PDO\DB;

class RepoDB implements RepoITF
{
    public static function byAccount(int $itemId, int $accountId, int $serverGroup): ?Price
    {
        $sql = "
            select * from uacc_prices 
            where accountId = :accountId 
                and itemId = :itemId 
                and serverGroupId = :serverGroupId";

        $params = ['accountId'     => $accountId,
                   'itemId'        => $itemId,
                   'serverGroupId' => $serverGroup];

        $price = DB::qwe($sql,$params)->fetchObject(Price::class);
        if (!$price) return null;
        $price->setMethod(Method::byAccount);
        return $price;
    }
}