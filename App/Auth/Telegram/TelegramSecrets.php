<?php

namespace App\Auth\Telegram;

readonly class TelegramSecrets
{
    public function __construct(public string $token, public string $bot_name)
    {
    }
}