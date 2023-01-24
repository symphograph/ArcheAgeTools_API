<?php

namespace Transfer;

use Api;
use Auth\Mailru\MailruUser;
use Symphograph\Bicycle\JsonDecoder;
use User\{Account, AccSettings, MailruOldUser, User};

class MailRuUserTransfer
{
    const apiDomain = 'dllib.ru';
    public static function byEmail(string $email): MailruOldUser|false
    {
        $url = 'https://' . self::apiDomain . '/api/get/user.php';
        $result = Api::curl($url, ['email'=>$email]);
        if(empty($result)){
            return false;
        }
        $result = json_decode($result);

        /** @var MailruOldUser $MailRuOldUser */
        $MailRuOldUser = JsonDecoder::cloneFromAny($result, MailruOldUser::class);
        return $MailRuOldUser;
    }

    public static function importUsers(): bool
    {
        if(!$EMails = self::getMailList()){
            return false;
        }
        foreach ($EMails as $email){
            if(!$MailRuOldUser = self::byEmail($email)){
                echo $email.' errorGet<br>';
                continue;
            }
            self::importOldMailRuUser($MailRuOldUser);
        }
        return true;
    }

    private static function getMailList(): array|false
    {
        $url = 'https://' . self::apiDomain . '/api/get/userlist.php';
        $result = Api::curl($url,[1,2]);
        if(empty($result)){
            echo 'Не вижу';
            return false;
        }
        return json_decode($result, 4);
    }

    private static function importOldMailRuUser(MailruOldUser $oldMailRuUser): bool
    {
        if(self::updateIfExist($oldMailRuUser)){
            echo $oldMailRuUser->email.' updated<br>';
            return true;
        }

        $newAccount = Account::create(authTypeId: 3);
        if(!self::saveMailUserDTO($oldMailRuUser, $newAccount)){
            echo $oldMailRuUser->email.' errorSave<br>';
            return false;
        }

        self::saveAccSets($oldMailRuUser, $newAccount);
        PriceTransfer::importPrices($oldMailRuUser->mail_id, $newAccount->id);

        echo $oldMailRuUser->email.' Added<br>';
        return true;
    }

    private static function updateIfExist(MailruOldUser $oldMailRuUser): bool
    {
        if($MailUser = MailruUser::byEmail($oldMailRuUser->email)){
            PriceTransfer::importPrices($oldMailRuUser->mail_id, $MailUser->accountId);
            echo $oldMailRuUser->email.' updated<br>';
            return true;
        }
        return false;
    }

    private static function saveMailUserDTO(MailruOldUser $oldMailRuUser, Account $newAccount): bool
    {

        $newMailUser = new MailruUser();

        $newMailUser->email = $oldMailRuUser->email;
        $newMailUser->first_name = $oldMailRuUser->first_name;
        $newMailUser->last_name = $oldMailRuUser->last_name;
        $newMailUser->first_time = $oldMailRuUser->time;
        $newMailUser->last_time = $oldMailRuUser->last_time ?? $oldMailRuUser->time;
        $newMailUser->nickname = $oldMailRuUser->mailnick;
        $newMailUser->name = $oldMailRuUser->mailnick;
        $newMailUser->image = $oldMailRuUser->avatar;
        $newMailUser->user_id = $newAccount->user_id;
        $newMailUser->accountId = $newAccount->id;

        if(!$newMailUser->putToDB()){
            User::delete($newAccount->user_id);
            return false;
        }
        return true;
    }

    private static function saveAccSets(MailruOldUser $oldMailRuUser, Account $newAccount): bool
    {
        $newAccount->AccSets->old_id = $oldMailRuUser->mail_id;
        $newAccount->AccSets->mode = $oldMailRuUser->mode ?? 1;
        $newAccount->AccSets->publicNick = $oldMailRuUser->user_nick ?? AccSettings::genNickName();
        $newAccount->AccSets->siol = boolval($oldMailRuUser->siol ?? 0);
        $newAccount->AccSets->serverId = $oldMailRuUser->server_id ?? 9;
        return $newAccount->AccSets->putToDB();
    }
}