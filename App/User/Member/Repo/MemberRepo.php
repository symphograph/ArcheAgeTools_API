<?php

namespace App\User\Member\Repo;

use App\User\Member\Member;
use App\User\Member\Repo\RepoITF;

class MemberRepo implements RepoITF
{

    static function get(int $accountId, int $serverGroupId): Member
    {
        $member = RepoMemory::get($accountId, $serverGroupId);
        if(!empty($member)) return $member;

        $member = RepoDB::get($accountId, $serverGroupId);
        RepoMemory::set($accountId, $serverGroupId, $member);
        return $member;
    }
}