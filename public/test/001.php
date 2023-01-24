<?php
$start = microtime(true);

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use User\{Account};
use Transfer\MailRuUserTransfer;

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


MailRuUserTransfer::importUsers();


echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
