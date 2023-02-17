<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Item\Price;
use App\User\Account;
$Account = Account::byToken();

$List = Price::basedList()
or die(Api::errorMsg('Не найдено'));

echo Api::resultData(['Prices'=>$List]);