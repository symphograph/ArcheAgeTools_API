<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\MyErrors;
use App\Packs\Zone;
use App\User\Account;

$Account = Account::byToken();
$zonesFrom = Zone::getFromsGroupBySide();


$allZonesTo = [];
foreach ($zonesFrom as $side) {
    foreach ($side as $zoneFrom){
        foreach ($zoneFrom->ZonesTo as $zoneTo){
            $allZonesTo[$zoneFrom->side][$zoneTo->id] = $zoneTo;
        }
    }
}
sort($allZonesTo[1]);
sort($allZonesTo[2]);
sort($allZonesTo[3]);

Response::data(['zonesFrom' => $zonesFrom, 'allZonesTo' => $allZonesTo]);