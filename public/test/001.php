<?php
$start = microtime(true);

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use App\User\{Account};
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

$Craft = \App\Transfer\Crafts\CraftDTO::byDB(8001170);
$Craft->putToDB();
//MailRuUserTransfer::importUsers();


echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
