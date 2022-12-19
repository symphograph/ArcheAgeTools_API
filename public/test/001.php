<?php
$start = microtime(true);

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use Item\Category;
use Craft\Craft;
use Item\Item;
use Symphograph\Bicycle\DB;
use Test\Test;
use User\MailruOldUser;

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525">
<?php
//printr($env);
//die('ttt');
$Account = \User\Account::bySess();
$Account->initMember();
$List = Item::searchList();
foreach ($List as $item){
    $Price = \Item\Price::getPrice($item->id,1);
    if(!$Price) continue;
    $Price->initLabel();
    echo $item->name . '<br>';
    printr($Price);
    echo '<hr>';
}
//printr($List);
//MailruOldUser::importOldMusers();
//Test::pricingByItemId();
/*
$Account = \User\Account::bySess();
$List = Item::searchList() or die(Api::errorMsg('pricingByItemId err'));
$i = 0;
foreach ($List as $item){
    $i++;
    if ($Price = \Item\Price::bySolo($item->id)) {
        //echo "<br>item_id: $item->id. err";
        printr($Price);
    }
    if($i > 10000) break;
}
*/
echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
