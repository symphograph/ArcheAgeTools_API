<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Debug;

$start = microtime(true);
echo Debug::header();


echo Debug::footer($start);
