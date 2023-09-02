<?php
$start = microtime(true);
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\DB;

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525">
<?php
$arr = [1];
for ($i = 4; $i > 0; $i--){
    $row = [1];
    foreach ($arr as $k => $n){
        $row[] = $n + ($arr[$k + 1] ?? 0);
    }
    $arr = $row;
}
printr($arr);
echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
