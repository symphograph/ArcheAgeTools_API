<?php
$start = microtime(true);
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account};
use App\DB;
use App\Item\Item;
use App\Test\Try\TryClass1;
use App\Transfer\MailRuUserTransfer;

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525">
<?php
//$Account = Account::bySess();
//$Account->initMember();
//$Account->AccSets->initProfs();

//$arr = getEmptyArr() or die('empty');
//$DB = new DB();
$qwe = qwe("select * from items where id = :id", ['id'=>'tyyyy']);
var_dump($qwe->fetchObject());
//MailRuUserTransfer::importUsers();

function getEmptyArr()
{
    return [];
}
echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
