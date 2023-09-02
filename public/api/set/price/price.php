<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Craft\AccountCraft;
use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\{ValidationErr};
use App\Item\Price;


$AccSets = AccSettings::byJwt();
$itemId = intval($_POST['itemId'] ?? 0)
or throw new ValidationErr('itemId', 'Ошибка данных');

$price = preg_replace("/[^0-9]/", '', $_POST['price'] ?? 0);
$price = intval($price);

if (empty($AccSets->serverGroup)) {
    throw new ValidationErr('Server is empty', 'Сервер не выбран');
}

$Price = Price::byInput($AccSets->accountId, $itemId, $AccSets->serverGroup, $price);
$Price->putToDB();

AccountCraft::clearAllCrafts();

Response::success();