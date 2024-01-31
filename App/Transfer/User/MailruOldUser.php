<?php

namespace App\Transfer\User;

use App\Api;
use App\Auth\Mailru\MailruUserClient;
use App\DTO\ItemDTO;
use App\DTO\PriceDTO;
use App\User\AccSettings;
use App\User\Server;
use Symphograph\Bicycle\DTO\BindTrait;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\CurlErr;
use Symphograph\Bicycle\Logs\ErrorLog;
use Symphograph\Bicycle\Logs\Log;


class MailruOldUser
{
    use BindTrait;
    const apiDomain = 'dllib.ru';

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
    public bool    $siol    = false;
    public ?string $user_nick;
    public ?string $avafile;
    public ?int    $mode;
    public ?int    $server_id;
    public array   $follows = [];
    public array   $prices  = [];

    public static function byEmail(string $email): self|bool
    {
        $url = 'https://' . self::apiDomain . '/api/get/user.php';
        $result = Api::curl($url, ['email'=>$email, 'method' => 'getByEmail']);
        if(empty($result)){
            ErrorLog::writeMsg('getByEmail is error');
            return false;
        }
        $result = json_decode($result);
        $MailRuOldUser = new self();
        $MailRuOldUser->bindSelf($result);
        return $MailRuOldUser;
    }

    public static function byId(string $mail_id): self|bool
    {
        $url = 'https://' . self::apiDomain . '/api/get/user.php';
        $result = Api::curl($url, ['mail_id'=>$mail_id, 'method' => 'getById'])
            or throw new AccountErr('oldSettings is error', 'Ошибка синхронизации настроек');

        $result = json_decode($result);
        return self::byBind($result);
    }

    public static function isExist(int $old_id): bool
    {
        $qwe = qwe("select accountId from uacc_settings where old_id = :old_id", ['old_id' => $old_id]);
        return !!$qwe->rowCount();
    }

    public static function getMailList(): array|false
    {
        $url = 'https://' . self::apiDomain . '/api/get/userlist.php';
        $result = Api::curl($url,[1,2]);
        if(empty($result)){
            throw new CurlErr('getMailList Err');
        }
        return json_decode($result, 4);
    }

    /**
     * @return self[]|false
     * @throws CurlErr
     */
    public static function getList(): array|false
    {
        $url = 'https://' . self::apiDomain . '/api/get/user.php';
        $result = Api::curl($url,['method' => 'list']);
        if(empty($result)){
            throw new CurlErr('getMailList Err');
        }
        $arr = json_decode($result, 4);
        $List = [];
        foreach ($arr as $data){
            $oldMailUser = self::byBind($data);
            $List[] = $oldMailUser;
        }
        return $List;
    }

    public function importFollows(int $accountId): void
    {
        if(empty($this->follows)) return;
        $serverGroupId = Server::getGroupId($this->server_id ?? 9);
        if($serverGroupId === 100){
            //FollowsTransfer::toAllServerGroups($accountId, $this->follows);
            return;
        }
        FollowsTransfer::import($accountId, $this->follows, $serverGroupId);
    }

    public function updateIfExist(): bool
    {
        $AccSets = AccSettings::byOldId($this->mail_id);
        if(!$AccSets){
            return false;
        }

        $this->importPrices($AccSets->accountId);
        $this->importFollows($AccSets->accountId);
        return true;
    }

    public function import(): bool
    {

        $MailUser = MailruUserClient::byOld($this);
        $AccSets = AccSettings::byOldData($this);


        $newUser = $MailUser->putToAuthServer(
            $this->time,
            $oldUser->last_time ?? $this->time
        );
        $AccSets->accountId = $newUser->accountId;
        $AccSets->avaFileName = $newUser->avaFilename;
        $AccSets->authType = 'mailru';
        $AccSets->putToDB();
        $this->importPrices($AccSets->accountId);
        return true;
    }

    public function importPrices(int $accountId): bool
    {
        if(empty($this->prices)){
            return false;
        }
        $i = 0;
        foreach ($this->prices as $price){
            $price = PriceDTO::byBind($price);
            $price->accountId = $accountId;
            if(empty($price->serverGroupId)){
                $price->serverGroupId = 100;
            }
            if($price->isExistNewerInDB()){
                //Log::msg("NewerPrice for item $price->itemId is exist");
                continue;
            }
            if(!ItemDTO::byId($price->itemId)){
                Log::msg("Item $price->itemId does not exist");
                continue;
            }
            $price->putToDB();
            $i++;
        }
        Log::msg("Imported $i Prices for $accountId");
        return true;
    }
}