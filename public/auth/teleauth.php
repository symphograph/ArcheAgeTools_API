<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Errors\{AccountErr, AuthErr};
use App\Auth\Telegram\{Telegram};
use App\User\{Sess, Account};

/**
 * Объект юзера с сервера Телеграм
 */
$TeleUser = Telegram::auth();
if(!$TeleUser){
    header('Location: auth/telelogin.php');
    die();
}

if ($Account = Account::byTelegram($TeleUser->id)){
    //Такой уже есть

}elseif($Account = Account::bySess()){
    $Account = $Account::create($Account->user_id,2);
}else{
    $Account = $Account::create(authTypeId: 2)
    or throw new AccountErr('Account::create Err','Ошибка создания акаунта');
}
$Account->saveTeleUser($TeleUser)
    or throw new AccountErr('saveTeleUser Err','Ошибка при сохранении');

$Sess = Sess::newSess($Account->id)
or throw new AuthErr('newSess Err','Ошибка создания сессии');

$Sess->goToClient();