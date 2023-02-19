<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\{AppErr, MyErrors};
use App\Item\{Currency, Price};
use App\User\Account;

$Account = Account::byToken();

$id = intval($_POST['id'] ?? 0)
    or Api::errorResponse('id', 400);

try{
    $Currency = Currency::byId($id)
        or throw new AppErr("Currency $id does not exist in DB", 'Валюта не найдена');

    $Currency->initTradableItems();
    if(!$Currency->initPrice()){
        $Currency->Price = new Price();
        $Currency->Price->icon = $Currency->icon;
        $Currency->Price->grade = $Currency->grade;
    }
    $Currency->initMonetisationItems();
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg());
}
Api::dataResponse($Currency);