<?php

use Craft\Craft;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
$itemId = intval($_POST['itemId'] ?? 0) or die('item_id');
$List = Craft::getList($itemId) or die(Api::errorMsg('Нет рецептов'));
echo Api::resultData($List);