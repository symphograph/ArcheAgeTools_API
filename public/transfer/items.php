<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use App\Test\Test;
use App\Transfer\ItemList;
use App\Transfer\PageItem;
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
    limit: 10000,
    itemId: 1,
    readOnly: false,
    random: false,
    onlyNew: false,
    onlyErrors: true
);

$ItemList->transferList();

echo Test::scriptTime($startTestTime);
?>
</body>
</html>
