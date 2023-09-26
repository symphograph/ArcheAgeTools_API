<?php

use App\CTRL\PriceCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

if (empty($_POST['method'])) {
    throw new ValidationErr();
}

match ($_POST['method']) {
    'listOfMember' => PriceCTRL::listOfMember(),
    'history' => PriceCTRL::history(),
    default => throw new ApiErr()
};