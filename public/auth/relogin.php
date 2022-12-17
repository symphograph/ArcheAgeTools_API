<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';
use User\{Account, Sess};
$id = intval($_GET['account_id'] ?? 0) or die();

$Account = Account::bySess() or die('Ошибка аутентификации');
$ascAccount = Account::byId($id) or die('Bad Error');
if ($Account->user_id !== $ascAccount->user_id){
    die('Wow!');
}

$Sess = Sess::newSess($ascAccount->id)
or die('Ошибка создания сессии');

$Sess->goToClient();