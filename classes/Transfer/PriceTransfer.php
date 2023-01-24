<?php

namespace Transfer;

use Api;
use Item\Price;
use Symphograph\Bicycle\JsonDecoder;

class PriceTransfer
{
    public static function importPrices(int $oldId, int $accountId): bool
    {
        if(!$Prices = self::getPrices($oldId, $accountId)){
            return false;
        }
        foreach ($Prices as $price){
            if(!$price->isExistNewerInDB()){
                $price->putToDB();
            }
        }
        return true;
    }

    private static function getResponse(int $oldId, string $lastDatetime): array|object|false
    {
        $result = Api::curl('https://dllib.ru/api/get/userprices.php',
            ['userId' => $oldId, 'lastDatetime'=> $lastDatetime]
        );
        if(empty($result)){
            return false;
        }
        return json_decode($result);
    }

    /**
     * @return array<Price>|false
     */
    private static function getPrices(int $oldId, int $accountId): array|false
    {
        $lastPrice = Price::getLastMemberPrice($accountId);
        if(!$result = self::getResponse($oldId, $lastPrice->datetime ?? '')){
            return false;
        }
        $Prices = [];
        foreach ($result as $price){
            /** @var Price $Price */
            $Price = JsonDecoder::cloneFromAny($price, Price::class);
            $Price->accountId = $accountId;
            $Prices[] = $Price;
        }
        return $Prices;
    }

}