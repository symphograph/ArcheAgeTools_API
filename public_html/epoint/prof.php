<?php

use App\Prof\ProfCTRL;
use Symphograph\Bicycle\Errors\ApiErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'setLvl' => ProfCTRL::setLvl(),
    default => throw new ApiErr()
};