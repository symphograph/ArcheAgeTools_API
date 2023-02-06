<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Item\Item;

$List = Item::searchList() or die(Api::errorMsg('err'));
echo Api::resultData($List);