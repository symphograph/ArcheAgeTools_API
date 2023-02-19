<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\MyErrors;
use App\Packs\Zone;
use App\User\Account;

$Account = Account::byToken();
try {
    $zonesFrom = Zone::getFromsGroupBySide();
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg());
}


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

Api::dataResponse(['zonesFrom' => $zonesFrom, 'allZonesTo' => $allZonesTo]);