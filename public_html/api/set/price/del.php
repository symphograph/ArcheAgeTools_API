<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\ValidationErr;
use App\Item\Price;

$AccSets = AccSettings::byJwt();
$itemId = intval($_POST['itemId'] ?? 0)
or throw new ValidationErr('itemId', 'Ошибка данных');

Price::delFromDB($AccSets->accountId, $itemId, $AccSets->serverGroupId);
AccountCraft::clearAllCrafts();

Response::success();