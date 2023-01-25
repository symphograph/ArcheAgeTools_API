<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use App\Api;
use App\Item\{Currency, Price};
use App\User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$id = intval($_POST['id'] ?? 0)
    or die(Api::errorMsg('id'));

$Currency = Currency::byId($id)
or die(Api::errorMsg('Валюта не найдена'));
$Currency->initTradableItems();
if(!$Currency->initPrice()){
    $Currency->Price = new Price();
    $Currency->Price->icon = $Currency->icon;
    $Currency->Price->grade = $Currency->grade;
}
$Currency->initMonetisationItems();
echo Api::resultData($Currency);