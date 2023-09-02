<?php
$start = microtime(true);
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Transfer\User\MailruOldUser;
use App\Transfer\User\MailRuUserTransfer;

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525">
<?php
$List = MailruOldUser::getList();
MailRuUserTransfer::importUsers(1000000);
//printr($List);


echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
