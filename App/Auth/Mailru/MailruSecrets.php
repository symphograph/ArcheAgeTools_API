<?php

namespace App\Auth\Mailru;

readonly class MailruSecrets
{
    public function __construct(public string $app_id, public string $app_secret)
    {
    }
}