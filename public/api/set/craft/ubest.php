<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use Craft\AccountCraft;
use User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$craftId = intval($_POST['craftId'] ?? 0)
or die(Api::errorMsg('craftId'));

AccountCraft::setUBest($Account->id, $craftId)
or die(Api::errorMsg('Ошибка при сохранении'));

AccountCraft::clearAllCrafts();

echo Api::resultMsg();