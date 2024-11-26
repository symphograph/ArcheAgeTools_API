<?php

use App\Currency\CurrencyCTRL;
use Symphograph\Bicycle\Errors\ApiErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'getTradeableItemIds' => CurrencyCTRL::getTradeableItemIds(),
    'getData' => CurrencyCTRL::getData(),
    default => throw new ApiErr()
};