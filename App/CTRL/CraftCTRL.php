<?php

namespace App\CTRL;

use App\Craft\AccountCraft;
use App\Craft\CraftCounter;
use App\Craft\CraftPool;
use App\Item\Price;
use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;

class CraftCTRL
{

    public static function getList(): void
    {
        AccSettings::byJwt();
        $itemId = intval($_POST['itemId'] ?? false)
        or throw new ValidationErr();

        if($Pool = CraftPool::getByCache($itemId)){
            Response::data($Pool);
        }

        $craftCounter = CraftCounter::recountList([$itemId]);
        if(!empty($craftCounter->lost)){
            $Lost = Price::lostList($craftCounter->lost);
            Response::data(['Lost' => $Lost]);
        }


        foreach ($craftCounter->countedItems as $resultItemId){
            CraftPool::getPoolWithAllData($resultItemId);
        }
        $Pool = CraftPool::getByCache($itemId)
        or throw new AppErr('CraftPool is empty', 'Рецепты не найдены');

        Response::data($Pool);
    }

    public static function setAsUBest(): void
    {
        $AccSets = AccSettings::byJwt();

        $craftId = intval($_POST['craftId'] ?? 0)
        or throw new ValidationErr('craftId');

        AccountCraft::setUBest($AccSets->accountId, $craftId);
        AccountCraft::clearAllCrafts();

        Response::success();
    }

    public static function resetUBest(): void
    {
        $AccSets = AccSettings::byJwt();
        $craftId = intval($_POST['craftId'] ?? 0)
        or throw new ValidationErr('craftId');

        AccountCraft::delUBest($AccSets->accountId, $craftId);
        AccountCraft::clearAllCrafts();

        Response::success();
    }
}