<?php

namespace App\Env;

use App\Auth\Mailru\MailruSecrets;
use App\Auth\Telegram\TelegramSecrets;

readonly class Env
{
    private array  $debugIPs;
    private bool   $debugMode;
    private int    $adminAccountId;
    private string $frontendDomain;
    private object $telegram;
    private object $mailruSecrets;


    public function __construct()
    {
        self::initEnv();
    }

    private function initEnv(): void
    {
        $env = require dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/env.php';
        $vars = (object)get_class_vars(self::class);
        foreach ($vars as $k => $v){
            if(!isset($env->$k)) continue;
            $this->$k = $env->$k;
        }
    }

    private static function getMyEnv(): self
    {
        global $Env;
        if(!isset($Env)){
            $Env = new self();
        }
        return $Env;
    }

    public static function isDebugIp(): bool
    {
        $Env = self::getMyEnv();
        return in_array($_SERVER['REMOTE_ADDR'], $Env->debugIPs);
    }

    public static function isDebugMode(): bool
    {
        $Env = self::getMyEnv();
        return $Env->debugMode && in_array($_SERVER['REMOTE_ADDR'], $Env->debugIPs);
    }

    public static function getAdminAccountId(): int
    {
        $Env = self::getMyEnv();
        return $Env->adminAccountId;
    }

    public static function getFrontendDomain(): int
    {
        $Env = self::getMyEnv();
        return $Env->frontendDomain;
    }

    public static function getTelegramSecrets(): TelegramSecrets
    {
        $Env = self::getMyEnv();
        return new TelegramSecrets($Env->telegram->token, $Env->telegram->bot_name);
    }

    public static function getMailruSecrets(): MailruSecrets
    {
        $Env = self::getMyEnv();
        return new MailruSecrets($Env->mailruSecrets->app_id, $Env->telegram->app_secret);
    }



}