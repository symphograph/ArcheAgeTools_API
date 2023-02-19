<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Craft\AccountCraft;
use App\Errors\AppErr;
use App\Errors\MyErrors;
use App\Errors\ValidationErr;
use App\User\Account;

$Account = Account::byToken();

try {
    $craftId = intval($_POST['craftId'] ?? 0)
        or throw new ValidationErr('craftId', 'Ошибка данных');

    AccountCraft::setUBest($Account->id, $craftId)
        or throw new AppErr('setUBest err', 'Ошибка при сохранении');

    AccountCraft::clearAllCrafts();
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg(), $err->getHttpStatus());
}

Api::resultResponse();