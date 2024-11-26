<?php

namespace App\Craft\Craft\Repo;

use App\Craft\Craft\Craft;
use App\Craft\Craft\CraftList;

class RepoDB implements RepoITF
{

    /**
     * @return Craft[]
     */
    static function getList(int $resultItemId): array
    {
        return CraftList::byResultItemId($resultItemId)->getList();
    }
}