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
$str = 'nu_f_bo_item_cloth177.png';
$fileBaseName = pathinfo($str, PATHINFO_BASENAME);
$dir = pathinfo($str, PATHINFO_DIRNAME);
if(in_array($dir,['.','..'])){
    $dir = '';
}
$arr = explode('_',$fileBaseName);
$result = '';
$i = 0;
foreach ($arr as $pit){$i++;
    $separator = mb_strlen($pit) > 2 ? '/' : '_';
    $result .= $i > 1 ? $separator . $pit : $pit;
}
echo $dir . '/' . $result;

//MailRuUserTransfer::importUsers();


echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
