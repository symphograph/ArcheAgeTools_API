<?php

use App\Api;
use Symphograph\Bicycle\Helpers;
use App\User\Account;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
$Account = Account::byToken($_POST['token'] ?? '') or die(Api::errorMsg('Обновите страницу'));
if (empty($_POST['nick'])){
    die(Api::errorMsg('Ой!'));
}

$nick = Helpers::sanitazeName($_POST['nick']);

if ($nick === $Account->AccSets->publicNick) {
    die(Api::resultMsg());
}

if (mb_strtolower($nick) !== mb_strtolower($Account->AccSets->publicNick)){
    if ($Account->AccSets::isNickExist($nick)) {
        die(Api::errorMsg('Ник занят'));
    }
}

if (mb_strlen($nick) > 20) {
    die(Api::errorMsg('Не больше 20'));
}

if (mb_strlen($nick) < 3) {
    die(Api::errorMsg('Не менее 3'));
}

if ($_POST['save'] ?? false) {
    $Account->AccSets->publicNick = $nick;
    $Account->AccSets->putToDB() or die(Api::errorMsg('Ошибка при сохранении'));
}

echo Api::resultMsg();