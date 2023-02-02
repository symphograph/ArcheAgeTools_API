<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use App\Test\Test;
use App\Transfer\Crafts\CraftList;
$startTestTime = Test::startTime();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525; font-family: Arial,serif; font-size: 12px">
<?php
set_time_limit(0);
$CraftList = new CraftList(
    craftId: 1,
    limit: 1000000,
    readOnly: false,
    random: false
);

$CraftList->transferErrorCrafts();

echo Test::scriptTime($startTestTime);
?>
</body>
</html>
