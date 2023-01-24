<?php

namespace User;

use Auth\Mailru\MailruUser;
use Item\Price;
use PDO;
use Transfer\MailRuUserTransfer;
use Transfer\PriceTransfer;

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
        return MailRuUserTransfer::byEmail($email);
    }
}