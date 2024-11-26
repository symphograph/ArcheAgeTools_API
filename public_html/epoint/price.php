<?php

use App\Price\PriceCTRL;
use Symphograph\Bicycle\Errors\ApiErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'listOfMember' => PriceCTRL::listOfMember(),
    'history' => PriceCTRL::history(),
    'del' => PriceCTRL::del(),
    'set' => PriceCTRL::set(),
    'get' => PriceCTRL::get(),
    'getBasedList' => PriceCTRL::getBasedList(),
    'getCurrency' => PriceCTRL::getCurrency(),
    default => throw new ApiErr()
};