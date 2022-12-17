<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use Item\Item;

$List = Item::searchList() or die(Api::errorMsg('err'));
echo Api::resultData($List);