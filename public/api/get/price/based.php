<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\MyErrors;
use App\Item\Price;
use App\User\Account;
$Account = Account::byToken();
try{
    $List = Price::basedList();
} catch (MyErrors $err) {
    Api::errorResponse('Предметы не найдены');
}
Api::dataResponse(['Prices'=>$List]);