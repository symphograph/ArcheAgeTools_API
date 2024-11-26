<?php

namespace App\Price\Repo;

use App\Item\Repo\ItemRepo;
use App\Price\Price;
use App\Price\Repo\RepoITF;

class PriceRepo implements RepoITF
{
    public static function byAccount(int $itemId, int $accountId, int $serverGroup): ?Price
    {
        $price = RepoMemory::get($itemId);
        if($price) return $price;

        $price = RepoDB::byAccount($itemId, $accountId, $serverGroup);
        if($price) {
            RepoMemory::set($price);
        }

        return $price;
    }

}