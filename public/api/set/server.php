<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$Account->AccSets->serverId = intval($_POST['server'] ?? 0)
or die(Api::errorMsg('Ой!'));

$Account->AccSets->putToDB()
or die(Api::errorMsg('Ошибка при сохранении'));

echo Api::resultMsg();