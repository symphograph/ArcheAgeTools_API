<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use Packs\Zone;
use User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

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