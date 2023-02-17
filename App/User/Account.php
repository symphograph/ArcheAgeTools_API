<?php

namespace App\User;

use App\Api;
use App\Errors\{AccountErr, AuthErr};
use App\Auth\{Discord\DiscordUser, Mailru\MailruUser, Telegram\TeleUser};
use App\Item\Item;
use PDO;
use Symphograph\Bicycle\DB;

class Account
{
    public int          $id;
    public int          $user_id;
    public int          $authTypeId;
    public ?string      $created;
    public ?string      $nickName;
    public ?string      $externalAvaUrl;
    public ?string      $avaFileName;
    public ?string      $label;
    public ?Sess        $Sess;
    public ?TeleUser    $TeleUser;
    public ?MailruUser  $MailruUser;
    public ?DiscordUser $DiscordUser;
    public ?AccSettings $AccSets;
    public ?Avatar      $Avatar;
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
            //var_dump($id);
            return false;
        }
        $Account = $qwe->fetchObject(self::class);

        if($Account->authTypeId === 1){
            $Account->externalAvaUrl = '/img/avatars/init_ava.png';
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

    public static function byToken(): self|bool
    {
        $token = $_POST['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        try
        {
            if(empty($token)){
                throw new AuthErr('empty token', 'Обновите страницу');
            }

            if(!$Sess = Sess::byToken($token)){
                throw new AuthErr('invalid token', 'Обновите страницу');
            }

            if(!$Account = self::byId($Sess->accountId)){
                throw new AccountErr("Account $Sess->accountId does not exist");
            }
        } catch (AuthErr $err) {
            Api::errorResponse($err->getResponseMsg(), 401);
        } catch (AccountErr $err) {
            Api::errorResponse($err->getResponseMsg());
        }
        return $Account;
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

    public static function byDiscord(int $discordId): self|false
    {
        if(!$DiscordUser = DiscordUser::byId($discordId)){
            return false;
        }
        if(!$Account = Account::byId($DiscordUser->accountId)){
            return false;
        }

        $Account->DiscordUser = $DiscordUser;
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

    public static function getSelf(): self
    {
        global $Account;
        try {
            if(!isset($Account)){
                throw new AccountErr('Account is not defined');
            }
        } catch (AccountErr $error){
            die(Api::errorMsg($error->getMessage()));
        }

        return $Account;
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
            $account->initOAuthUserData();
            $account->initAvatar();
            $List[] = $account;
        }
        return $List;
    }

    public function initOAuthUserData(): bool
    {
        $result = match ($this->authTypeId){
            1 => true,
            2 => self::initTeleUser(),
            3 => self::initMailruUser(),
            4 => self::initDiscordUser(),
            default => false
        };
        if(!$result){
            throw new AccountErr("err initOAuthUserData for user $this->user_id");
        }
        return $result;
    }

    private function initTeleUser(): bool
    {
        if(!($TeleUser = TeleUser::byAccountId($this->id))){
            return false;
        }
        $this->TeleUser = $TeleUser;
        $this->externalAvaUrl = $TeleUser->photo_url;
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
        $this->externalAvaUrl = $MailruUser->image;
        $this->label = 'mail.ru';
        $this->nickName = $MailruUser->getNickName();
        return true;

    }

    private function initDiscordUser(): bool
    {
        if(!($DiscordUser = DiscordUser::byAccountId($this->id))){
            return false;
        }
        $this->DiscordUser = $DiscordUser;
        $this->externalAvaUrl = "https://cdn.discordapp.com/avatars/$DiscordUser->id/$DiscordUser->avatar.png";
        $this->label = 'discord';
        $this->nickName = $DiscordUser->username;
        return true;

    }

    private function unsetOAuthUserData(): void
    {
        unset($this->MailruUser);
        unset($this->TeleUser);
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
        if(!$member = Member::byId($this->id, $this->AccSets->serverGroup)){
            return false;
        }
        $this->Member = $member;
        return true;
    }

    public function initAvatar(): void
    {
        $Avatar = false;
        if(!($this->authTypeId > 1)){
            $this->Avatar = new Avatar();
            return;
        }
        if(empty($this->avaFileName)){
            self::initOAuthUserData();
            $Avatar = Avatar::byExternalUrl($this->externalAvaUrl);
            if($Avatar){
                $this->avaFileName = $Avatar->fileName;
                self::putToDB();
            }
            self::unsetOAuthUserData();
        }

        if(!$Avatar){
            $Avatar = Avatar::byAvaFileName($this->avaFileName);
        }
        if(!$Avatar){
            $Avatar = new Avatar();
        }
        $this->Avatar = $Avatar;
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
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'authTypeId' => $this->authTypeId,
            'created'    => $this->created,
            'avaFileName'    => $this->avaFileName ?? null
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

    public function saveDiscordUser(DiscordUser $DiscordUser): bool
    {
        $DiscordUser->accountId = $this->id;
        return $DiscordUser->putToDB();
    }
}