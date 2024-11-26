<?php

use App\CTRL\PowerCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'put' => PowerCTRL::put(),
    'getByContact' => PowerCTRL::get(),
    default => throw new ApiErr()
};
