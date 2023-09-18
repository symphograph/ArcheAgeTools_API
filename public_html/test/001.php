<?php
$start = microtime(true);
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Craft\AccountCraft;
use App\Craft\CraftCounter;
use App\Item\Item;
use App\Test\Test;
use App\Transfer\Items\ItemTransLog;
use App\Transfer\User\MailruOldUser;
use App\Transfer\User\MailRuUserTransfer;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Helpers;
use Symphograph\Bicycle\SQL\SQLBuilder;

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525; font-family: Arial,serif; font-size: 14px">
<?php
//$List = MailruOldUser::getList();
//MailRuUserTransfer::importUsers(1000000);
/*
$AccSets = \App\User\AccSettings::byIdAndInit(1057);
AccountCraft::clearAllCrafts();
$craftCounter = CraftCounter::recountList([48251]);
*/

$Test = new App\Test\Test();
$qwe = qwe("select id, name, categId from items");
$list = $qwe->fetchAll();
echo 'TestMedian: ' . $Test->speedTestTime('sortFunction2', 100, $list);

//printr($craftCounter);

echo Test::scriptTime($start);
//echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
