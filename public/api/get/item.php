<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\AppErr;
use App\Errors\MyErrors;
use App\Errors\RequestErr;
use App\Item\Item;
use App\User\Account;

$Account = Account::byToken();
$Account->initMember();

try{
    $id = intval($_POST['id'] ?? 0) or
        throw new RequestErr('invalid id');

    $Item = Item::byId($id)
        or throw new AppErr("item $id does not exist in DB",'Предмет не найден');

    $Item->initInfo();
    $Item->Info->initCategory($Item->categId);
    $Item->initPricing();
    $Item->Pricing->Price->initItemProps();
    $Item->initIsBuyOnly();
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg());
}
Api::dataResponse($Item);