<?php

use Craft\Craft;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
$item_id = intval($_POST['item_id'] ?? 0) or die('item_id');
$List = Craft::getList($item_id) or die(Api::errorMsg('Нет рецептов'));
echo Api::resultData($List);