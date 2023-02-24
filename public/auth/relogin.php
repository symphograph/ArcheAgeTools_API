<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account, Sess};
use Symphograph\Bicycle\Errors\{AccountErr, AuthErr, ValidationErr};

$id = intval($_GET['accountId'] ?? 0)
or throw new ValidationErr('accountId is empty', 'Ошибка данных');

$Account = Account::bySess()
or throw new AuthErr('bySess err', 'Ошибка аутентификации');

$ascAccount = Account::byId($id)
or throw new AccountErr("invalid Account $id", 'Ошибка аккаунта');

if ($Account->user_id !== $ascAccount->user_id) {
    throw new AccountErr("Account: $Account->user_id != ascAccount:  $ascAccount->user_id", 'Wow!');
}

$Sess = Sess::newSess($ascAccount->id)
or throw new AuthErr("newSess err ascAccount $ascAccount->id", 'Ошибка создания сессии');

$Sess->goToClient();