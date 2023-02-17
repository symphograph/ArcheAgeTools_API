<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Packs\Zone;
use App\User\Account;

$Account = Account::byToken();

$zonesFrom = Zone::getFromsGroupBySide()
or die(Api::errorMsg('Локации не найдены'));
$allZonesTo = [];
foreach ($zonesFrom as $side)
{
    foreach ($side as $zoneFrom){
        foreach ($zoneFrom->ZonesTo as $zoneTo){
            $allZonesTo[$zoneFrom->side][$zoneTo->id] = $zoneTo;
        }

        //printr($zoneFrom->ZonesTo);
    }
}
sort($allZonesTo[1]);
sort($allZonesTo[2]);
sort($allZonesTo[3]);

echo Api::resultData(['zonesFrom' => $zonesFrom, 'allZonesTo' => $allZonesTo]);