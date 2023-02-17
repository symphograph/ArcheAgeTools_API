<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Craft\AccountCraft;
use App\Item\Price;
use App\User\Account;

$Account = Account::byToken();

$itemId = intval($_POST['itemId'] ?? 0)
or die(Api::errorMsg('id'));

$price = preg_replace("/[^0-9]/", '', $_POST['price'] ?? 0);
$price = intval($price);


if (!$Account->AccSets->serverGroup){
    die(Api::errorMsg('Сервер не выбран'));
}
$Price = Price::byInput($Account->id, $itemId, $Account->AccSets->serverGroup, $price);
$Price->putToDB()
or die(Api::errorMsg('Ошибка при сохранении'));

AccountCraft::clearAllCrafts();
echo Api::resultMsg();