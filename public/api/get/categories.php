<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use Item\Category;
$List = Category::getTree();
echo Api::resultData($List);