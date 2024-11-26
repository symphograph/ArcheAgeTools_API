<?php

namespace App\Currency\Repo;

interface RepoITF
{
    static function getTradeableIds(int $currencyId);
}