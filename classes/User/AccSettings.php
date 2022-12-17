<?php

namespace User;

use Symphograph\Bicycle\DB;

class AccSettings
{
    public int      $account_id   = 0;
    public int      $server_id    = 9;
    public int      $server_group = 2;
    public string   $publicNick   = 'Никнейм';
    public int      $grade        = 1;
    public int      $mode         = 1;
    public ?int $old_id;
    public bool $siol = false;

    public function __set(string $name, $value): void
    {
    }

    //Get-----------------------------------------------------------
    public static function byId(int $account_id): self|bool
    {
        if (!$account_id) return false;

        $qwe = qwe("select * from uacc_settings where account_id = :id", ['id' => $account_id]);
        if (!$qwe || !$qwe->rowCount()) {
            return self::getDefault($account_id);
        }

        $AccSets = $qwe->fetchObject(self::class);
        $AccSets->initServerGroup();
        return $AccSets;
    }

    public static function getDefault(int $account_id): self
    {
        $AccSets = new self();
        $AccSets->account_id = $account_id;
        $AccSets->publicNick = self::genNickName();
        return $AccSets;
    }

    public static function byOld(int $account_id): self|bool
    {
        $Account = Account::byId($account_id, true);
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
        $Server = Server::byId($this->server_id);
        $this->server_group = $Server->group ?? 2;
    }


    //Save-----------------------------------------------------------
    public function putToDB(): bool
    {
        $params = [
            'account_id' => $this->account_id,
            'server_id'  => $this->server_id,
            'publicNick' => $this->publicNick,
            'grade'      => $this->grade,
            'mode'       => $this->mode,
            'siol' => intval($this->siol),
            'old_id'     => $this->old_id ?? null
        ];
        return DB::replace('uacc_settings', $params);
    }
}