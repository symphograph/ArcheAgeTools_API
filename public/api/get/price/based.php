<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\MyErrors;
use App\Item\Price;
use App\User\Account;
$Account = Account::byToken();
$List = Price::basedList();

Response::data(['Prices'=>$List]);