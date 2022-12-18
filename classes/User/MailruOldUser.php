<?php

namespace User;

use Auth\Mailru\MailruUser;
use Item\Price;
use PDO;

class MailruOldUser
{
    public ?int    $mail_id;
    public ?string $first_name;
    public ?string $last_name;
    public ?int    $age;
    public ?string $email;
    public ?string $time;
    public ?string $last_time;
    public ?string $avatar;
    public ?string $mailnick;
    public ?string $ip;
    public ?string $last_ip;
    public ?string $identy;
    public ?string $token;
    public bool    $siol = false;
    public ?string $user_nick;
    public ?string $avafile;
    public ?int    $mode;
    public ?int    $server_id;

    public static function byEmail(string $email): self|bool
    {
        $qwe = qwe("select * from old_mailusers where email is not null AND email = :email",['email' => $email]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    /**
     * @return array<self>|bool
     */
    public static function getList(): array|bool
    {
        $qwe = qwe("
            select old_mailusers.*,
                    if(servers.server, servers.server, 9) as server_id
            from old_mailusers
                 left join old_user_servers servers 
                     on old_mailusers.mail_id = servers.user_id
            where email is not null"
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
    }

    public static function importOldMusers(): void
    {
        if(!($List = self::getList())){
            return;
        }
        foreach ($List as $oldMuser){
            self::importOldMuser($oldMuser);
        }
    }

    private static function importOldMuser(self $oldMuser): void
    {
        if(MailruUser::byEmail($oldMuser->email)){
            return;
        }
        $newAccount = Account::create(authTypeId: 3);
        $newMailUser = new MailruUser();

        $newMailUser->email = $oldMuser->email;
        $newMailUser->first_name = $oldMuser->first_name;
        $newMailUser->last_name = $oldMuser->last_name;
        $newMailUser->first_time = $oldMuser->time;
        $newMailUser->last_time = $oldMuser->last_time ?? $oldMuser->time;
        $newMailUser->nickname = $oldMuser->mailnick;
        $newMailUser->name = $oldMuser->mailnick;
        $newMailUser->image = $oldMuser->avatar;
        $newMailUser->user_id = $newAccount->user_id;
        $newMailUser->accountId = $newAccount->id;

        if(!$newMailUser->putToDB()){
            User::delete($newAccount->user_id);
            return;
        }
        $newAccount->AccSets->old_id = $oldMuser->mail_id;
        $newAccount->AccSets->mode = $oldMuser->mode ?? 1;
        $newAccount->AccSets->publicNick = $oldMuser->user_nick ?? AccSettings::genNickName();
        $newAccount->AccSets->siol = boolval($oldMuser->siol ?? 0);
        $newAccount->AccSets->serverId = $oldMuser->server_id ?? 9;
        $newAccount->AccSets->putToDB();

        $Prices = Price::getOldList($newAccount->AccSets->old_id);
        if(empty($Prices)){
            return;
        }
        foreach ($Prices as $price){
            $price->accountId = $newAccount->id;
            $price->putToDB();
        }
    }

    public function saveAsNew(MailruUser $mailruUser)
    {
        $mailruUser->first_time = $this->time;
    }
}