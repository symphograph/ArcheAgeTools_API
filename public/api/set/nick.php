<?php

use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Errors\ValidationErr;
use App\User\PublicNick;
use App\User\Account;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
$Account = Account::byToken();

if (empty($_POST['nick'])){
    throw new ValidationErr('nick', 'Ой!');
}

$pubNick = new PublicNick($_POST['nick']);

if ($pubNick->nick === $Account->AccSets->publicNick) {
    Response::success();
}

$pubNick->validation($Account);

if ($_POST['save'] ?? false) {
    $Account->AccSets->publicNick = $pubNick->nick;
    $Account->AccSets->putToDB()
    or throw new AppErr('putToDB err', 'Ошибка при сохранении');
}

Response::success();