<?php

use App\Category\CategoryCTRL;
use Symphograph\Bicycle\Errors\ApiErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'get' => CategoryCTRL::get(),
    'getListAsTree' => CategoryCTRL::getListAsTree(),
    default => throw new ApiErr()
};