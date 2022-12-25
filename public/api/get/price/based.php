<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$List = \Item\Price::basedList()
or die(Api::errorMsg('Не найдено'));

echo Api::resultData(['Prices'=>$List]);