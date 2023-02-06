<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Test\Test;
use App\Transfer\Items\ItemList;

$startTestTime = Test::startTime();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525">
<?php
set_time_limit(0);
$ItemList = new ItemList(
    itemId: 46578,
    onlyNew: false,
    limit: 1,
    readOnly: false,
    random: false
);


$ItemList->transferItems();
$errorFilter = [
    /*'ItemPage is empty',*/
    'Item is overdue',
    /*'content not received'*/
    /*'Item is unnecessary',*/
    /*'Category is unnecessary'*/
];
//$ItemList->transferErrorItems();
echo Test::scriptTime($startTestTime);
?>
</body>
</html>
