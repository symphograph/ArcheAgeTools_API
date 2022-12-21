<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$item_id = intval($_POST['itemId'] ?? 0)
or die(Api::errorMsg('id'));

$price = preg_replace("/[^0-9]/", '', $_POST['price'] ?? 0);
$price = intval($price)
or die(Api::errorMsg('price'));

$Price = \Item\Price::byInput($Account->id,$item_id,$Account->AccSets->serverGroup, $price);
$Price->putToDB() or die(Api::errorMsg('Ошибка при сохранении'));

echo Api::resultMsg();