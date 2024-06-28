<?php

use App\CTRL\AccSetsCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;


require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (empty($_POST['method'])) {
    throw new ValidationErr();
}

match ($_POST['method']) {
    'get' => AccSetsCTRL::get(),
    'list' => AccSetsCTRL::list(),
    default => throw new ApiErr()
};