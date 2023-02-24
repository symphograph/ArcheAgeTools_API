<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Craft\AccountCraft;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\{AppErr, MyErrors, ValidationErr};
use App\Item\Price;
use App\User\Account;

$Account = Account::byToken();
$itemId = intval($_POST['itemId'] ?? 0)
or throw new ValidationErr('itemId', 'Ошибка данных');

$price = preg_replace("/[^0-9]/", '', $_POST['price'] ?? 0);
$price = intval($price);

if ($Account->AccSets->serverGroup) {
    throw new ValidationErr('Server is empty', 'Сервер не выбран');
}

$Price = Price::byInput($Account->id, $itemId, $Account->AccSets->serverGroup, $price);
$Price->putToDB()
or throw new AppErr('Save err', 'Ошибка при сохранении');

AccountCraft::clearAllCrafts();

Response::success();