<?php
set_time_limit(0);
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Test\Test;
use App\Transfer\Deep\DeepFinder;


$startTestTime = Test::startTime();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>deep</title>
</head>
<body style="color: white; background-color: #262525; font-family: Arial,serif; font-size: 14px">
<?php

//$AccSets = AccSettings::byId();


$Deeper = new DeepFinder();
$result = $Deeper->execute();
if(!$result){
    printr('badCraft: ' . $Deeper->badCraft);
}
echo Test::scriptTime($startTestTime);
?>
</body>
</html>
