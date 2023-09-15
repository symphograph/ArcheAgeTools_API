<?php
$start = microtime(true);
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Item\Item;
use App\Test\Test;
use App\Transfer\Items\ItemTransLog;
use App\Transfer\User\MailruOldUser;
use App\Transfer\User\MailRuUserTransfer;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Helpers;
use Symphograph\Bicycle\SQL\SQLBuilder;

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525; font-family: Arial,serif; font-size: 14px">
<?php
//$List = MailruOldUser::getList();
//MailRuUserTransfer::importUsers(1000000);

class SomeClass
{
    //deep1
    public static function myFunc1(): void {self::myFunc3();}

    //deep1
    public static function myFunc2(): void {self::myFunc4();}

    //-------------------------------------------------------
    //deep2
    private static function myFunc3(): void{self::myFunc5();}

    //deep2
    private static function myFunc4(): void{self::myFunc6();}

    //-------------------------------------------------------
    //deep3
    private static function myFunc5(){}

    //deep3
    private static function myFunc6(){}
}



echo Test::scriptTime($start);
//echo '<br>Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
?>
</body>
