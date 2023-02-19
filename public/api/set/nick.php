<?php

use App\Api;
use App\Errors\AppErr;
use App\Errors\MyErrors;
use App\Errors\ValidationErr;
use App\User\PublicNick;
use Symphograph\Bicycle\Helpers;
use App\User\Account;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
$Account = Account::byToken();

try {

    if (empty($_POST['nick'])){
        throw new ValidationErr('nick', 'Ой!');
    }

    $pubNick = new PublicNick($_POST['nick']);

    if ($pubNick->nick === $Account->AccSets->publicNick) {
        Api::resultResponse();
    }

    $pubNick->validation($Account);

    if ($_POST['save'] ?? false) {
        $Account->AccSets->publicNick = $pubNick->nick;
        $Account->AccSets->putToDB()
            or throw new AppErr('putToDB err', 'Ошибка при сохранении');
    }
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg(), $err->getHttpStatus());
}

Api::resultResponse();