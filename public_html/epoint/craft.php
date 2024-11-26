<?php

use App\Craft\Craft\CraftCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (empty($_POST['method'])) {
    throw new ValidationErr();
}

match ($_POST['method']) {
    'getList' => CraftCTRL::getList(),
    'setAsUBest' => CraftCTRL::setAsUBest(),
    'resetUBest' => CraftCTRL::resetUBest(),
    default => throw new ApiErr()
};