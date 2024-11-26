<?php

use App\User\Member\MemberCTRL;
use Symphograph\Bicycle\Errors\ApiErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'list' => MemberCTRL::list(),
    'get' => MemberCTRL::get(),
    'followToggle' => MemberCTRL::followToggle(),
    default => throw new ApiErr()
};