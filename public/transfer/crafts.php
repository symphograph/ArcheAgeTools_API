<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Test\Test;
use App\Transfer\Crafts\TransferCrafts;
$startTestTime = Test::startTime();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>craftTransfer</title>
</head>
<body style="color: white; background-color: #262525; font-family: Arial,serif; font-size: 12px">
<?php
set_time_limit(0);
$CraftList = new TransferCrafts(
    startId: 1,
    limit: 10,
    readOnly: true,
    random: true
);
$errFilter = ['content not received'];
//$CraftList->transferErrorCrafts();
$CraftList->transferNewCrafts();

echo Test::scriptTime($startTestTime);
?>
</body>
</html>
