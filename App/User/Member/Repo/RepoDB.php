<?php

namespace App\User\Member\Repo;

use App\User\Member\Member;

class RepoDB implements RepoITF
{

    static function get(int $accountId, int $serverGroupId): Member
    {
        return Member::newInstance($accountId)->initFollowMasters($serverGroupId);
    }
}