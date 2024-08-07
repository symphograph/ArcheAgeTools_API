<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Item\{Currency};
use App\Price\Price;
use App\User\AccSets;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\{AppErr, ValidationErr};

$AccSets = AccSets::byJwt();

$id = intval($_POST['id'] ?? 0)
    or throw new ValidationErr('bad id');

//if($id === 500) throw new ValidationErr('Currency is gold');

$Currency = Currency::byId($id)
or throw new AppErr("Currency $id does not exist in DB", 'Валюта не найдена');

$Currency->initTradableItems();
if(!$Currency->initPrice()){
    $Currency->Price = new Price();
    $Currency->Price->icon = $Currency->icon;
    $Currency->Price->grade = $Currency->grade;
}
$Currency->initMonetisationItems();

Response::data($Currency);