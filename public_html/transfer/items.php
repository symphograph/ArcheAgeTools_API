<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Test\Test;
use App\Transfer\Items\TransferItems;

$startTestTime = Test::startTime();

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>transfer Items</title>
</head>
<body style="color: white; background-color: #262525">
<?php
set_time_limit(0);
$ItemList = new TransferItems(
    startId: 100,
    limit: 1000000,
    readOnly: false,
    random: false
);



$errorFilter = [
    /*'Category is unnecessary',*/
    /*'ItemPage is empty',*/
    /*'Item is overdue',*/
    'content not received'
    /*'Item is unnecessary',*/
    /*'Category is unnecessary'*/
];
//$ItemList->transferErrorItems($errorFilter);
//$ItemList->transferExistingItems();

$ItemList->transferNewItems();
echo Test::scriptTime($startTestTime);
?>
</body>
</html>
