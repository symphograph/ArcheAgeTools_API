<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';
use App\Auth\Telegram\{Telegram, TeleUser};
use App\User\{Sess, Account};

/**
 * Объект юзера с сервера Телеграм
 */
$TeleUser = Telegram::auth($env);
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
    or die('Ошибка создания акаунта');
}
$Account->saveTeleUser($TeleUser)
or die('Ошибка при сохранении');

$Sess = Sess::newSess($Account->id)
or die('Ошибка создания сессии');

$Sess->goToClient();