<?php

use App\CTRL\PriceCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (empty($_POST['method'])) {
    throw new ValidationErr();
}

match ($_POST['method']) {
    'listOfMember' => PriceCTRL::listOfMember(),
    'history' => PriceCTRL::history(),
    'del' => PriceCTRL::del(),
    'set' => PriceCTRL::set(),
    'getBasedList' => PriceCTRL::getBasedList(),
    default => throw new ApiErr()
};