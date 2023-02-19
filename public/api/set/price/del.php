<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Craft\AccountCraft;
use App\Errors\MyErrors;
use App\Errors\ValidationErr;
use App\Item\Price;
use App\User\Account;
$Account = Account::byToken();
try {
    $itemId = intval($_POST['itemId'] ?? 0)
        or throw new ValidationErr('itemId', 'Ошибка данных');

    Price::delFromDB($Account->id, $itemId, $Account->AccSets->serverGroup);
    AccountCraft::clearAllCrafts();
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg(), $err->getHttpStatus());
}

Api::resultResponse();