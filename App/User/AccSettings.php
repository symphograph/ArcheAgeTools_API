<?php

namespace App\User;

use App\Item\Price;
use App\Craft\LaborData;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;


class AccSettings
{
    public int    $accountId   = 0;
    public int    $serverId    = 9;
    public int    $serverGroup = 2;
    public string $publicNick  = 'Никнейм';
    public int    $grade       = 1;
    public int    $mode        = 1;
    public ?int   $old_id;
    public bool   $siol        = false;
    /**
     * @var array<Prof>|null
     */
    public ?array $Profs;
    public ?int $laborCost;

    public function __set(string $name, $value): void
    {
    }

    //Get-----------------------------------------------------------
    public static function byId(int $accountId): self|bool
    {
        if (!$accountId) return false;

        $qwe = qwe("select * from uacc_settings where accountId = :id", ['id' => $accountId]);
        if (!$qwe || !$qwe->rowCount()) {
            return self::getDefault($accountId);
        }

        $AccSets = $qwe->fetchObject(self::class);
        $AccSets->initServerGroup();
        return $AccSets;
    }

    public static function getDefault(int $accountId): self
    {
        $AccSets = new self();
        $AccSets->accountId = $accountId;
        $AccSets->publicNick = self::genNickName();
        return $AccSets;
    }

    public static function byOld(int $accountId): self|bool
    {
        $Account = Account::byId($accountId);
        $Account->initOAuthUserData();

        if($Account->authTypeId !== 3){
            return false;
        }
        if(!empty($Account->AccSets->old_id)){
            //Его данные уже брали. Переписывать не нужно.
            return false;
        }
        if(!($OldUser = MailruOldUser::byEmail($Account->MailruUser->email ?? ''))){
           return false;
        }

        $Account->AccSets->mode = $OldUser->mode;
        $Account->AccSets->publicNick = $OldUser->user_nick;
        $Account->AccSets->old_id = $OldUser->mail_id;
        return $Account->AccSets;
    }


    //Self-----------------------------------------------------------
    public static function genNickName(): string
    {
        $nick = NickNameGenerator::getNickName();
        if (self::isNickExist($nick)) {
            $nick = self::genNickName();
        }
        return $nick;
    }

    public static function isNickExist(string $nick): bool
    {
        $qwe = qwe("select * from uacc_settings where lower(publicNick) = lower(:nick)", ['nick' => $nick]);
        if($qwe && $qwe->rowCount()){
            return true;
        }
        $qwe = qwe("select * from old_mailusers where lower(user_nick) = lower(:nick)", ['nick' => $nick]);
        return ($qwe && $qwe->rowCount());
    }

    public function initServerGroup(): void
    {
        $Server = Server::byId($this->serverId);
        $this->serverGroup = $Server->group ?? 2;
    }

    public function initLaborCost(): void
    {
        if($Price = Price::bySaved(2)){
            $this->laborCost = $Price->price;
            return;
        }
        $this->laborCost = LaborData::defaultLaborCost;
    }

    public function getLaborCost(): int
    {
        if(empty($this->laborCost)){
            self::initLaborCost();
        }
        return $this->laborCost;
    }

    public function initProfs(): void
    {
        $profs = Prof::getAccountProfs($this->accountId);
        if($profs){
            $this->Profs = $profs;
        }
    }


    //Save-----------------------------------------------------------
    public function putToDB(): bool
    {
        $params = [
            'accountId' => $this->accountId,
            'serverId'  => $this->serverId,
            'publicNick' => $this->publicNick,
            'grade'      => $this->grade,
            'mode'       => $this->mode,
            'siol'       => intval($this->siol),
            'old_id'     => $this->old_id ?? null
        ];
        try {
            $qwe = DB::replace('uacc_settings', $params)
                or throw new AppErr('putToDB err', 'Ошибка при сохранении');
        } catch (AppErr $err) {
            Response::error($err->getResponseMsg());
        }
        return  !!$qwe;

    }
}