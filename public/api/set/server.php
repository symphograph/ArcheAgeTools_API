<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\{AppErr, MyErrors, ValidationErr};
use App\User\Account;

$Account = Account::byToken();

$Account->AccSets->serverId = intval($_POST['server'] ?? 0)
or throw new ValidationErr('server', 'Ошибка данных');

$Account->AccSets->putToDB()
or throw new AppErr('putToDB err', 'Ошибка при сохранении');

Response::success();