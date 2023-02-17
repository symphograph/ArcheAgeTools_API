<?php

namespace App\Auth\Discord;

readonly class DiscordSecrets
{
    public function __construct(
        public string $clientId,
        public string $clientSecret
    )
    {
    }
}