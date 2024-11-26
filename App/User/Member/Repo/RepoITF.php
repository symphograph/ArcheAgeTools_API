<?php

namespace App\User\Member\Repo;

use App\User\Member\Member;

interface RepoITF
{
    static function get(int $accountId, int $serverGroupId): ?Member;
}