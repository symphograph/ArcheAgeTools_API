<?php

namespace App\Craft\Craft;

use App\Craft\CraftCounter;
use App\Craft\CraftPool;
use App\Craft\UCraft\UCraft;
use App\Price\Price;
use App\User\AccSets;
use App\User\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\HTTP\Request;

class CraftCTRL
{

    public static function getList(): void
    {
        User::auth();
        Request::checkEmpty(['itemId']);

        $Pool = CraftPool::getByCache($_POST['itemId']);
        if (!empty($Pool)) {
            Response::data($Pool);
        }

        $craftCounter = CraftCounter::recountList([$_POST['itemId']]);
        if (!empty($craftCounter->lost)) {
            $Lost = Price::lostList($craftCounter->lost);
            Response::data(['Lost' => $Lost]);
        }


        foreach ($craftCounter->countedItems as $resultItemId) {
            CraftPool::getPoolWithAllData($resultItemId);
        }
        $Pool = CraftPool::getByCache($_POST['itemId'])
        or throw new AppErr('CraftPool is empty', 'Рецепты не найдены');

        Response::data($Pool);
    }

    #[NoReturn] public static function setAsUBest(): void
    {
        User::auth();
        Request::checkEmpty(['craftId']);
        $AccSets = AccSets::$current;

        UCraft::setUBest($AccSets->accountId, $_POST['craftId']);
        UCraft::clearAllCrafts();

        Response::success();
    }

    #[NoReturn] public static function resetUBest(): void
    {
        User::auth();
        Request::checkEmpty(['craftId']);
        $AccSets = AccSets::$current;

        UCraft::delUBest($AccSets->accountId, $_POST['craftId']);
        UCraft::clearAllCrafts();

        Response::success();
    }
}