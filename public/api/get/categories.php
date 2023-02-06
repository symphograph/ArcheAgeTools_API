<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Item\Category;

$List = Category::getTree();
echo Api::resultData($List);