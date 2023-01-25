<?php

namespace App\Auth\Mailru;

use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\JsonDecoder;

class MailruUser
{
    public ?int    $id;
    public ?int    $user_id;
    public ?int    $accountId;
    public ?string $client_id;
    public ?string $gender;
    public ?string $name;
    public ?string $nickname;
    public ?string $first_name;
    public ?string $last_name;
    public ?string $locale;
    public ?string $email;
    public ?string $birthday;
    public ?string $image;
    public ?string $first_time;
    public ?string $last_time;

    public function __set(string $name, $value): void
    {
    }

    public static function checkClass($Object): self
    {
        return $Object;
    }

    public static function byEmail(string $email): self|bool
    {
        $qwe = qwe("select * from user_mailru where email = :email", ['email'=>$email]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from user_mailru where id = :id", ['id'=>$id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byAccountId(int $id): self|bool
    {
        $qwe = qwe("select * from user_mailru where accountId = :id", ['id'=>$id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byMailruToken(string $token): self|bool
    {
        $url = 'https://oauth.mail.ru/userinfo' . '?access_token=' . $token;
        if (!($user = @file_get_contents($url))) {
            return false;
        }
        $user = json_decode($user);
        $user = JsonDecoder::cloneFromAny($user,self::class);
        return self::checkClass($user);
    }

    public function getNickName(): string
    {
        if($this->nickname){
            return $this->nickname;
        }

        return trim(($this->first_time ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function putToDB(): bool
    {
        $params =         [
            'id'         => $this->id ?? null,
            'user_id'    => $this->user_id,
            'accountId' => $this->accountId,
            'gender'     => $this->gender ?? null,
            'name'       => $this->name ?? null,
            'nickname'   => $this->nickname ?? null,
            'first_name' => $this->first_name ?? null,
            'last_name'  => $this->last_name ?? null,
            'locale'     => $this->locale ?? null,
            'email'      => $this->email ?? null,
            'birthday'   => date('Y-m-d', strtotime($this->birthday ?? '')),
            'image'      => $this->image ?? null,
            'first_time' => $this->first_time,
            'last_time' => $this->last_time ?? date('Y-m-d H:i:s')

        ];
        return DB::replace('user_mailru', $params);
    }
}