<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use App\Item\Item;
use Symphograph\Bicycle\Errors\AppErr;

$List = Item::searchList()
    or throw new AppErr('searchList is empty', 'Предметы не найдены');

Response::data($List);