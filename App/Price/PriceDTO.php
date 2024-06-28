<?php

namespace App\Price;

use Symphograph\Bicycle\DTO\DTOTrait;
use Symphograph\Bicycle\PDO\DB;

class PriceDTO
{
    use DTOTrait;

    const string tableName = 'uacc_prices';

    public int    $accountId;
    public int    $serverGroupId;
    public int    $itemId;
    public int    $price;
    public string $updatedAt;

    public static function del(int $accountId, int $itemId, int $serverGroupId): void
    {
        $sql = "
            delete from uacc_prices 
            where accountId = :accountId 
                and itemId = :itemId 
                and serverGroupId = :serverGroupId";

        $params = compact('accountId', 'itemId', 'serverGroupId');

        DB::qwe($sql, $params);
    }

    public function isExistNewerInDB(): bool
    {
        $qwe = "
            select * 
            from uacc_prices 
            where accountId = :accountId
            and serverGroupId = :serverGroupId
            and itemId = :itemId
            and updatedAt >= :updatedAt";

        $params = [
            'accountId'     => $this->accountId,
            'serverGroupId' => $this->serverGroupId,
            'itemId'        => $this->itemId,
            'updatedAt'     => $this->updatedAt
        ];
        $qwe = qwe($qwe, $params);
        return $qwe && $qwe->rowCount();
    }

    protected function afterPut(): void
    {
        PriceLog::put($this);
    }
}