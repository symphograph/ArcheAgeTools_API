<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Test\Test;
use App\Transfer\Items\ItemTransferAgent;
use App\Transfer\TransParams;

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
$params = new TransParams(
    startId: null,
    limit: 0,
    readOnly: false,
    random: false);

$ItemList = new ItemTransferAgent($params);



$errorFilter = [
    'Category does not exist in DB: Костюмы дару',
    /*'Category is unnecessary',*/
    /*'ItemPage is empty',*/
    /*'Item is overdue',*/
    /*'content not received',*/
    /*'ItemErr: ItemPage is empty'*/
    /*'Item is unnecessary',*/
    /*'Category is unnecessary'*/
];

//$ItemList->transferErrorItems($errorFilter);
$ItemList->transferEmptyIcons();
//$ItemList->transferExistingItems();
//$ItemList->transferNewItems();
echo Test::scriptTime($startTestTime);
?>
</body>
</html>
