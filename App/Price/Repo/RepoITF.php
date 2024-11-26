<?php

namespace App\Price\Repo;

use App\Price\Price;

interface RepoITF
{
    public static function byAccount(int $itemId, int $accountId, int $serverGroup): ?Price;
}