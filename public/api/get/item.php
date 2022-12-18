<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use Item\Item;
use User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$id = intval($_POST['id'] ?? 0) or
die(Api::errorMsg('id'));

$Item = Item::byId($id) or die(Api::errorMsg('Предмет не найден'));
$Item->initInfo();
$Item->Info->initCategory($Item->categId);
$Item->initPrice();
$Item->initPricing();
echo Api::resultData($Item);
