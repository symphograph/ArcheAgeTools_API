<?php
$start = microtime(true);

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use Item\{Category, Currency, Item};
use Craft\{Craft, CraftCounter};
use Packs\Pack;
use Symphograph\Bicycle\DB;
use Test\Test;
use User\{MailruOldUser, Account};

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525">
<?php
$Account = Account::bySess();
$Account->initMember();
$Account->AccSets->initProfs();
//Test::countPackCrafts();
//Test::countAllCrafts();

$list = \Packs\PackIds::getUncounted();
printr($list);

echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
