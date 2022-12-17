<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\Account;
$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$Account->AccSets->mode = intval($_POST['mode'] ?? 0)
or die(Api::errorMsg('Ой!'));

$Account->AccSets->putToDB()
or die(Api::errorMsg('Ошибка при сохранении'));

echo Api::resultMsg();