<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Item\Item;

$List = Item::searchList()
    or Api::errorResponse('Предметы не найдены');

Api::dataResponse($List);