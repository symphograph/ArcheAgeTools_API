<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Debug;
use App\User\Member;

$start = microtime(true);
echo Debug::header();
$list = Member::getList(1093, 2);
printr($list);

echo Debug::footer($start);



