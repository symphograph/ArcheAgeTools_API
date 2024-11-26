<?php

use App\Item\ItemCTRL;
use Symphograph\Bicycle\Errors\ApiErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'get' => ItemCTRL::get(),
    'getResultItemIds' => ItemCTRL::getResultItemIds(),
    'addToBuyable' => ItemCTRL::addToBuyable(),
    'delFromBuyable' => ItemCTRL::delFromBuyable(),
    default => throw new ApiErr()
};