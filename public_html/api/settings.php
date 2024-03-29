<?php

use App\CTRL\AccSettingsCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;


require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (empty($_POST['method'])) {
    throw new ValidationErr();
}

match ($_POST['method']) {
    'get' => AccSettingsCTRL::get(),
    'list' => AccSettingsCTRL::list(),
    default => throw new ApiErr()
};