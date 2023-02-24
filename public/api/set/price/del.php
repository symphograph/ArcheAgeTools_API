<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Errors\ValidationErr;
use App\Item\Price;
use App\User\Account;
$Account = Account::byToken();

$itemId = intval($_POST['itemId'] ?? 0)
or throw new ValidationErr('itemId', 'Ошибка данных');

Price::delFromDB($Account->id, $itemId, $Account->AccSets->serverGroup);
AccountCraft::clearAllCrafts();

Response::success();