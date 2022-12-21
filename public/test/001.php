<?php
$start = microtime(true);

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use Item\Category;
use Craft\Craft;
use Item\Item;
use Symphograph\Bicycle\DB;
use Test\Test;
use User\MailruOldUser;

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525">
<?php
$List = \Item\Price::memberPriceList(83,1);
printr($List);
echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
