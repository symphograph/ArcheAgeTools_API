<?php

namespace App\Price;

use Symphograph\Bicycle\DTO\BindTrait;
use Symphograph\Bicycle\PDO\DB;

class PriceLog extends PriceDTO
{
    use BindTrait;
    const string tableName = 'uacc_priceLog';
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
        DB::replace(self::tableName, self::getAllProps());
    }
}