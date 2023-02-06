<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Item\Item;
use App\User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));
$Account->initMember();

$id = intval($_POST['id'] ?? 0) or
die(Api::errorMsg('id'));

$Item = Item::byId($id) or die(Api::errorMsg('Предмет не найден'));
$Item->initInfo();
$Item->Info->initCategory($Item->categId);
$Item->initPricing();
$Item->Pricing->Price->initItemProps();
$Item->initIsBuyOnly();
echo Api::resultData($Item);
