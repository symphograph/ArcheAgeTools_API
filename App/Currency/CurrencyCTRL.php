<?php

namespace App\Currency;

use App\Currency\Repo\CurrencyRepo;
use App\Item\ItemList;
use App\User\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\HTTP\Request;

class CurrencyCTRL
{

    #[NoReturn] public static function getTradeableItemIds(): void
    {
        User::auth();
        Request::checkEmpty(['currencyId']);
        $ids = CurrencyRepo::getTradeableIds($_POST['currencyId']);

        Response::data($ids);
    }

    #[NoReturn] public static function getData(): void
    {
        User::auth();
        Request::checkEmpty(['currencyId']);

        $currency = Currency::byId($_POST['currencyId'])
            ->initTradeableItems()
            ->initMonetizationItems();

        Response::data($currency);
    }
}