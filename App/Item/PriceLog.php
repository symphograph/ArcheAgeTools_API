<?php

namespace App\Item;

use App\DTO\PriceDTO;
use Symphograph\Bicycle\DB;

class PriceLog extends PriceDTO
{
    const tableName = 'uacc_priceLog';
    public string $createDate;

    public static function put(PriceDTO $priceDTO): void
    {
        $PriceLog = new self();
        $PriceLog->bindSelf($priceDTO);
        $PriceLog->createDate = date('Y-m-d', strtotime($priceDTO->updatedAt));
        $PriceLog->putToDB();
    }

    public function putToDB(): void
    {
        $params = DB::initParams($this);
        DB::replace(self::tableName, $params);
    }
}