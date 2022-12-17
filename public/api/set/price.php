<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$item_id = intval($_POST['item_id'] ?? 0)
or die(Api::errorMsg('id'));

$price = intval($_POST['price'] ?? 0)
or die(Api::errorMsg('price'));

$Price = \Item\Price::byInput($Account->id,$item_id,$Account->AccSets->server_group, $price);
$Price->putToDB() or die(Api::errorMsg('Ошибка при сохранении'));

echo Api::resultMsg();