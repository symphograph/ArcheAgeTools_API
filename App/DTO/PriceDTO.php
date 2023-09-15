<?php

namespace App\DTO;

use App\Item\PriceLog;
use PDO;
use Symphograph\Bicycle\DB;

class PriceDTO extends DTO
{
    const tableName = 'uacc_prices';
    public int $accountId;
    public int $serverGroup;
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
            and serverGroup = :serverGroup
            and itemId = :itemId
            and updatedAt >= :updatedAt", [
                'accountId'   => $this->accountId,
                'serverGroup' => $this->serverGroup,
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
                     and serverGroup = :serverGroup",
            ['accountId' => $accountId, 'itemId' => $itemId, 'serverGroup' => $serverGroup]
        );
    }

}