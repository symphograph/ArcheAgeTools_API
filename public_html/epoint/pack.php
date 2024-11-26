<?php

use App\Packs\PackCTRL;
use Symphograph\Bicycle\Errors\ApiErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

match ($_POST['method']) {
    'list' => PackCTRL::list(),
    default => throw new ApiErr()
};