<?php

use App\Item\ItemList;
use Symphograph\Bicycle\Debug\Debug;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

$Debug = new Debug();
$Debug->printHeader();
//------------------------------------------------------------------------------------------------------------------
$list = ItemList::byIds([100,101,102]);
vd($list->getList());
//printr($list->getList());


//------------------------------------------------------------------------------------------------------------------
$Debug->printFooter();



