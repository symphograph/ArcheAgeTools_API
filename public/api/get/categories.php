<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\MyErrors;
use App\Item\Category;
try {
    $List = Category::getTree();
} catch (MyErrors $err) {
    Api::errorResponse('Категории не загрузились');
}
Api::dataResponse($List);