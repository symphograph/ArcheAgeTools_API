<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use User\{Account, Prof};
use Craft\AccountCraft;

$Account = Account::byToken($_POST['token'] ?? '')
    or die(Api::errorMsg('Обновите страницу'));

$profId = intval($_POST['profId'] ?? 0)
    or die(Api::errorMsg('profId'));

$lvl = intval($_POST['lvl'] ?? 0)
    or die(Api::errorMsg('lvl'));

Prof::saveLvl($Account->id, $profId, $lvl)
    or die(Api::errorMsg('Ошибка при сохранении'));

AccountCraft::clearAllCrafts();

echo Api::resultMsg();