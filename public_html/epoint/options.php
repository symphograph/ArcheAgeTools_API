<?php

use App\CTRL\OptionsCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'getMain' => OptionsCTRL::getMain(),
    'getZones' => OptionsCTRL::getZones(),
    'getCategories' => OptionsCTRL::getCategories(),
    'getSearchList' => OptionsCTRL::getSearchList(),
    default => throw new ApiErr()
};