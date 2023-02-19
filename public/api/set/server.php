<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\AppErr;
use App\Errors\MyErrors;
use App\Errors\ValidationErr;
use App\User\Account;

$Account = Account::byToken();

try {
    $Account->AccSets->serverId = intval($_POST['server'] ?? 0)
        or throw new ValidationErr('server', 'Ошибка данных');

    $Account->AccSets->putToDB()
        or throw new AppErr('putToDB err', 'Ошибка при сохранении');
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg(), $err->getHttpStatus());
}

Api::resultResponse();