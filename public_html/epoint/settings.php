<?php

use App\User\AccSetsCTRL;
use Symphograph\Bicycle\Errors\ApiErr;


require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'get' => AccSetsCTRL::get(),
    'list' => AccSetsCTRL::list(),
    'setNick' => AccSetsCTRL::setNick(),
    'setServerGroup' => AccSetsCTRL::setServerGroup(),
    'setMode' => AccSetsCTRL::setMode(),
    default => throw new ApiErr()
};