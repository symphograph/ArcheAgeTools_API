<?php

namespace App\Auth\Discord;

use Symphograph\Bicycle\DB;

class DiscordUser
{
    public int     $id;
    public int $accountId;
    public string  $username;
    public ?string $display_name;
    public ?string $avatar;
    public ?string $avatar_decoration;
    public int     $discriminator;
    public ?int    $public_flags;
    public ?int    $flags;
    public ?string $banner;
    public ?int    $banner_color;
    public ?int    $accent_color;
    public ?string $locale;
    public bool    $verified = false;
    public ?string $email;
    public ?int    $premium_type;
    public bool    $mfa_enabled = false;
    //public bool    $system = false;
    public bool    $bot = false;

    public static function byId(int $id)
    {
        $qwe = qwe("select * from user_discord where id = :id", ['id' => $id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byAccountId(int $accountId)
    {
        $qwe = qwe("select * from user_discord where accountId = :accountId", ['accountId' => $accountId]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public function putToDB(): bool
    {
        $params = [];
        foreach ($this as $k => $v){
            if($v === null) continue;
            $v = is_bool($this->$k) ? intval($v) : $v;
            $params[$k] = $v;
        }
        return match (true){
            empty($this->id),
            empty($this->discriminator) => false,
            default => DB::replace('user_discord', $params)
        };
    }
}