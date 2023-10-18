<?php

namespace App\User;

use App\Craft\LaborData;
use App\DTO\AccSettingsDTO;
use App\Item\Price;
use App\Transfer\User\MailruOldUser;
use App\UserStorage;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Logs\Log;
use Symphograph\Bicycle\Token\AccessTokenData;


class AccSettings extends AccSettingsDTO
{
    use ModelTrait;
    /**
     * @var array<Prof>|null
     */
    public ?array $Profs;
    public ?int $laborCost;


    //Get-----------------------------------------------------------

    public static function byOldId(int $old_id): self|bool
    {
        $qwe = qwe("select * from uacc_settings where old_id = :old_id", ['old_id' => $old_id]);
        return $qwe->fetchObject(self::class);
    }

    public static function byJwt(): self|false
    {
        global $AccSets;
        $accountId = AccessTokenData::accountId();
        $AccSets = AccSettings::byIdAndInit($accountId);
        return $AccSets;
    }

    public static function byGlobal(): self
    {
        global $AccSets;
        if(empty($AccSets)){
            throw new AccountErr('AccSets is not defined');
        }
        return $AccSets;
    }

    public static function getDefault(int $accountId): self
    {
        $AccSets = new self();
        $AccSets->accountId = $accountId;
        $AccSets->publicNick = self::genNickName();
        return $AccSets;
    }

    public static function byOldServer(int $accountId, string $email): self|bool
    {
        $OldUser = MailruOldUser::byEmail($email);
        if(empty($OldUser)){
            Log::msg('MailruOldUser is empty');
            return false;
        }
        $AccSets = self::byOldData($OldUser);
        $AccSets->accountId = $accountId;
        $AccSets->putToDB();
        $AccSets->initData();
        $OldUser->importPrices($accountId);
        $OldUser->importFollows($accountId);

        return $AccSets;
    }

    public static function byOldData(MailruOldUser $OldUser): self
    {
        $AccSets = new self();
        $AccSets->mode = $OldUser->mode;
        $AccSets->publicNick = $OldUser->user_nick ?? self::genNickName();
        $AccSets->old_id = $OldUser->mail_id;
        $AccSets->serverGroupId = Server::getGroupId($OldUser->server_id);
        $AccSets->authType = 'mailru';
        return $AccSets;
    }

    //Self-----------------------------------------------------------
    public function initData(): void
    {
        global $AccSets;
        if(!empty($AccSets)){
            self::initLaborCost();
        }
        self::initProfs();
    }

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
        /*
        $qwe = qwe("select * from old_mailusers where lower(user_nick) = lower(:nick)", ['nick' => $nick]);
        return ($qwe && $qwe->rowCount());
        */
        return in_array($nick, UserStorage::getSelf()->oldNicks);
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

}