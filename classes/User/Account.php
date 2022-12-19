<?php

namespace User;

use Api;
use Auth\Mailru\MailruUser;
use Auth\Telegram\TeleUser;
use Item\Item;
use PDO;
use Symphograph\Bicycle\DB;

class Account
{
    public int     $id;
    public int     $user_id;
    public int     $authTypeId;
    public ?string $created;

    public ?string      $nickName;
    public ?string      $avatar;
    public ?string      $label;
    public ?Sess        $Sess;
    public ?TeleUser    $TeleUser;
    public ?MailruUser  $MailruUser;
    public ?AccSettings $AccSets;
    public ?Member      $Member;

    /*public function __set(string $name, $value): void
    {
    }
*/

    //Get------------------------------------------------------------------------------------
    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from user_accounts where id = :id", ['id' => $id]);
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        $Account = $qwe->fetchObject(self::class);

        if($Account->authTypeId === 1){
            $Account->avatar = '/img/avatars/init_ava.png';
            $Account->nickName = 'Не авторизован';
        }
        if (!$Account->initSettings()) {
            return false;
        }
        return $Account;
    }

    public static function bySess(): self|bool
    {
        if(empty($_COOKIE['sessId'])){
            return false;
        }

        if(!($Sess = Sess::byId($_COOKIE['sessId']))){
            return false;
        }

        if(!($Account = self::byId($Sess->accountId))){
            return false;
        }
        $Account->Sess = $Sess;
        return $Account;
    }

    public static function byToken(string $token): self|bool
    {
        if(empty($token))
            return false;
        if(!($Sess = Sess::byToken($token))){
            return false;
        }

        return self::byId($Sess->accountId);
    }

    public static function byTelegram(int $tele_id): self|bool
    {
        if(!($TeleUser = TeleUser::byTeleId($tele_id))){
            return false;
        }

        if(!($Account = Account::byId($TeleUser->accountId))){
            return false;
        }
        $Account->TeleUser = $TeleUser;
        return $Account;
    }

    public static function byMailRu(string $email): self|bool
    {
        if(!($MailruUser = MailruUser::byEmail($email))){
            return false;
        }

        if(!($Account = Account::byId($MailruUser->accountId))){
            return false;
        }

        $Account->MailruUser = $MailruUser;
        return $Account;
    }

    /**
     * @return array<self>
     */
    public static function getList(int $user_id): array
    {
        $qwe = qwe("
            select * from user_accounts 
            where user_id = :user_id and authTypeId > 1",
            ['user_id'=>$user_id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        $Accounts = $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
        return self::initDataInList($Accounts);
    }

    /**
     * @return array<self>
     */
    public static function getServerList(int $serverGroup, int $accountId): array
    {
       /*
        $qwe = qwe("
            select user_accounts.id from user_accounts
                inner join uacc_settings us on user_accounts.id = us.accountId
                 inner join servers on us.server_id = servers.id
                   and servers.`group` = :serverGroup
                                    LIMIT 100",

            ['serverGroup'=>$serverGroup, 'accountId'=> $accountId]
        );
       */

        $privateItemsStr = implode(',', Item::privateItems());
        $qwe = qwe("select 
                        uAcc.*,
                        cnt,
                        /*uAcc.avatar as remote_avalink,
                        avafile, */
                        lastTime, 
                        (uf.master > 0) as isfolow,
                        flwt.flws
                        from 
                        (
                            select accountId, COUNT(*) as cnt, max(datetime) as lastTime 
                            from uacc_prices
                            where serverGroup = :serverGroup
                                and itemId not in ( $privateItemsStr )
                            group by accountId
                            order by lastTime desc 
                        ) as tmp
                        inner join user_accounts uAcc on uAcc.id = tmp.accountId
                            and uAcc.authTypeId > 1
                        left join uacc_follows uf on uf.master = uAcc.id and uf.follower = :accountId
                        left join 
                            (
                                select count(*) as flws, uf.follower, uf.master  
                                from uf 
                                group by uf.master
                            ) as flwt 
                        ON uAcc.id = flwt.master
                        order by isfolow desc, YEAR(lastTime) desc, MONTH(lastTime) desc, WEEK(lastTime,1) desc, (cnt>50) desc, lastTime desc
                        LIMIT 100",['serverGroup'=>$serverGroup]);
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        $Accounts = $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
        return self::initDataInList($Accounts);
    }


    //Self-----------------------------------------------------------------

    /**
     * @param array<self> $Accounts
     * @return array<self>
     */
    private static function initDataInList(array $Accounts): array
    {
        $List = [];
        foreach ($Accounts as $account){
            if(!($account = Account::byId($account->id))){
                continue;
            }
            $List[] = $account;
        }
        return $List;
    }

    public function initOAuthUserData(): bool
    {
        if($this->authTypeId === 2){
            return self::initTeleUser();
        }
        if($this->authTypeId === 3){
            return self::initMailruUser();
        }
        return false;
    }

    private function initTeleUser(): bool
    {
        if(!($TeleUser = TeleUser::byAccountId($this->id))){
            return false;
        }
        $this->TeleUser = $TeleUser;
        $this->avatar = $TeleUser->photo_url;
        $this->label = 'Телеграм';
        $this->nickName = ($TeleUser->first_name ?? '') . ' ' . ($TeleUser->last_name ?? '');
        $this->nickName = trim($this->nickName);
        return true;
    }

    private function initMailruUser(): bool
    {
        if(!($MailruUser = MailruUser::byAccountId($this->id))){
            return false;
        }
        $this->MailruUser = $MailruUser;
        $this->avatar = $MailruUser->image;
        $this->label = 'mail.ru';
        $this->nickName = $MailruUser->getNickName();
        return true;

    }

    private function initSettings(): bool
    {
        if(!($AccSets = AccSettings::byId($this->id))){
            return false;
        }
        $this->AccSets = $AccSets;
        return true;
    }

    public function initMember(): bool{
        if(!($member = Member::byId($this->id))){
            return false;
        }
        $this->Member = $member;
        return true;
    }


    //Save-----------------------------------------------------------------------
    public static function create(int $user_id = 0, int $authTypeId = 1): bool|self
    {
        $Account = new self();

        if(!($Account->id = DB::createNewID('user_accounts', 'id'))){
            return false;
        }
        if(!$user_id){
            $User = User::create() or die('Ошибка создания пользователя');
            $user_id = $User->id;
        }
        $Account->user_id = $user_id;
        $Account->authTypeId = $authTypeId;
        $Account->created = date('Y-m-d H:i:s');
        $Account->AccSets = AccSettings::getDefault( $Account->id);
        if(!$Account->putToDB()){
            return false;
        }
        return $Account;
    }

    private function putToDB(): bool
    {
        $params = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'authTypeId' => $this->authTypeId,
            'created' => $this->created
        ];
        if(!DB::replace('user_accounts', $params)){
            return false;
        }
        if(!empty($this->AccSets)){
            return $this->AccSets->putToDB();
        }
        return true;
    }

    public static function auth(): self|bool
    {
        if (!($Account = Account::bySess())) {
            $Account = Account::create()
            or die('Ошибка создания акаунта');

            $Account->Sess = Sess::newSess($Account->id)
            or die('Ошибка создания сессии');
        } else {
            $Account->Sess->refresh() or die('Ошибка обновления сессии');
        }
        return $Account;
    }

    public function saveTeleUser(TeleUser $TeleUser): bool
    {
        $TeleUser->auth_date = date('Y-m-d H:i:s', intval($TeleUser->auth_date));
        $TeleUser->user_id = $this->user_id;
        $TeleUser->accountId = $this->id;
        return $TeleUser->putToDB();
    }

    public function saveMailruUser(MailruUser $MailruUser): bool
    {
        $MailruUser->last_time = date('Y-m-d H:i:s');
        $MailruUser->user_id = $this->user_id;
        $MailruUser->accountId = $this->id;
        return $MailruUser->putToDB();
    }
}