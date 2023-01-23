<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use Item\Price;
use User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$List = Price::basedList()
or die(Api::errorMsg('Не найдено'));

echo Api::resultData(['Prices'=>$List]);