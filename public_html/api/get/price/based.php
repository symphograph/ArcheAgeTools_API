<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use App\Item\Price;


$AccSets = AccSettings::byJwt();
$List = Price::basedList();

Response::data(['Prices'=>$List]);