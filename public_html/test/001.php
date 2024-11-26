<?php
ini_set('memory_limit', '512M');

use App\Craft\CraftCounter;
use App\Item\IconIMG;
use App\Item\Item;
use App\Item\ItemList;
use App\Mat\Mat;
use App\Packs\PackIds;
use App\Transfer\Items\ItemTransLogList;
use App\User\AccSets;
use Symphograph\Bicycle\Debug\Debug;
use Symphograph\Bicycle\PDO\PutMode;


require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

$Debug = new Debug();
$Debug->printHeader();
//------------------------------------------------------------------------------------------------------------------
/*
$itemList = ItemList::all();
$craftList = \App\Craft\CraftList::all();
$matList = \App\Mat\MatList::bySql("select * from craftMaterials");
$prices = \Symphograph\Bicycle\PDO\DB::qwe("select * from uacc_prices");
echo 'dl';
*/
function ttt(string $t = Item::tableName)
{

}

//------------------------------------------------------------------------------------------------------------------
$Debug->printFooter();



