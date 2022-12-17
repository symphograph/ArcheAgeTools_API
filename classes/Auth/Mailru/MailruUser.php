<?php

namespace Auth\Mailru;

use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\JsonDecoder;

class MailruUser
{
    public int|null    $id;
    public int|null    $user_id;
    public int|null    $account_id;
    public string|null $client_id;
    public string|null $gender;
    public string|null $name;
    public string|null $nickname;
    public string|null $first_name;
    public string|null $last_name;
    public string|null $locale;
    public string|null $email;
    public string|null $birthday;
    public string|null $image;
    public string|null $first_time;
    public string|null $last_time;

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
        $qwe = qwe("select * from user_mailru where account_id = :id", ['id'=>$id]);
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
            'account_id' => $this->account_id,
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