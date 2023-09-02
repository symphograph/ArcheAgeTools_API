<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use App\Item\Item;
use Symphograph\Bicycle\Errors\ValidationErr;


$AccSets = AccSettings::byJwt();
$id = intval($_POST['id'] ?? 0)
or throw new ValidationErr('invalid id');

$Item = Item::byId($id)
or throw new AppErr("item $id does not exist in DB",'Предмет не найден');

$Item->initInfo();
$Item->Info->initCategory($Item->categId);
$Item->initPricing();
$Item->Pricing->Price->initItemProps();
$Item->initIsBuyOnly();

Response::data($Item);