<?php

namespace App\DTO;

use App\Item\PriceLog;
use Symphograph\Bicycle\DB;

class PriceDTO extends DTO
{
    const tableName = 'uacc_prices';
    public int $accountId;
    public int $serverGroupId;
    public int $itemId;
    public int $price;
    public string $updatedAt;

    public function putToDB(): void
    {
        $params = DB::initParams($this);
        DB::replace(self::tableName, $params);
        PriceLog::put($this);
    }

    public static function byBind(object| array $Object): PriceDTO
    {
        $selfObject = new self();
        $selfObject->bindSelf($Object);
        return $selfObject;
    }

    public function isExistNewerInDB(): bool
    {
        $qwe = qwe("
            select * 
            from uacc_prices 
            where accountId = :accountId
            and serverGroupId = :serverGroupId
            and itemId = :itemId
            and updatedAt >= :updatedAt", [
                'accountId'   => $this->accountId,
                'serverGroupId' => $this->serverGroupId,
                'itemId'      => $this->itemId,
                'updatedAt'    => $this->updatedAt
            ]
        );
        return $qwe && $qwe->rowCount();
    }

    public static function del(int $accountId, int $itemId, int $serverGroup): void
    {
        qwe("
            delete from uacc_prices 
                   where accountId = :accountId 
                     and itemId = :itemId 
                     and serverGroupId = :serverGroupId",
            ['accountId' => $accountId, 'itemId' => $itemId, 'serverGroupId' => $serverGroup]
        );
    }

}