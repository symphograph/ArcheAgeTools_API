<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Env\Env;
use App\Auth\Mailru\{OAuthMailRu};
use App\User\{Account, AccSettings, Sess};
use App\Transfer\PriceTransfer;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\AuthErr;

$secret = Env::getMailruSecrets();


if (!empty($_GET['error'])) {
    throw new AuthErr($_GET['error']);
}

if (empty($_GET['code'])) {
    // Самый первый запрос
    OAuthMailRu::goToAuth($secret);
}

// Пришёл ответ без ошибок после запроса авторизации
if (!OAuthMailRu::getToken($_GET['code'], $secret)) {
    throw new AuthErr('Error - no token by code');
}
/*
 * На данном этапе можно проверить зарегистрирован ли у вас MailRu-юзер с id = OAuthMailRu::$user_id
 * Если да, то можно просто авторизовать его и не запрашивать его данные.
 */

$nMailruUser = OAuthMailRu::getUser();
$nMailruUser->first_time = date('Y-m-d H:i:s');

if ($Account = Account::byMailRu($nMailruUser->email)) {
    //Такой уже есть
    //printr($Account);
    $nMailruUser->first_time = $Account->MailruUser->first_time;

} elseif ($Account = Account::bySess()) {
    $Account = $Account::create($Account->user_id, 3);
} else {
    $Account = $Account::create(authTypeId: 3)
        or throw new AccountErr('Account::create Err','Ошибка создания акаунта');
}

$Account->saveMailruUser($nMailruUser)
    or throw new AccountErr('saveMailruUser Err','Ошибка при сохранении');

$Sess = Sess::newSess($Account->id)
    or throw new AuthErr('newSess Err','Ошибка создания сессии');

//----------------------------------------------------------
if ($AccSets = AccSettings::byOld($Account->id)) {
    $AccSets->putToDB();
    PriceTransfer::importPrices($AccSets->old_id, $Account->id);
}


$Sess->goToClient();