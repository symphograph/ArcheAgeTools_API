<?php

namespace App\User\Member\Repo;

use App\User\Member\Member;
use App\User\Member\Repo\RepoITF;

class RepoMemory implements RepoITF
{
    static array $members = [];

    static function get(int $accountId, int $serverGroupId): ?Member
    {
        return self::$members[$accountId][$serverGroupId] ?? null;
    }

    static function set(int $accountId, int $serverGroupId, Member $member): void
    {
        self::$members[$accountId][$serverGroupId] = $member;
    }
}