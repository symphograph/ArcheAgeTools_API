<?php

namespace App\Craft\Craft\Repo;

class CraftRepo implements RepoITF
{

    /**
     * @inheritDoc
     */
    static function getList(int $resultItemId): array
    {
        $crafts = RepoMemory::getList($resultItemId);
        if(!empty($crafts)) return $crafts;

        $crafts = RepoDB::getList($resultItemId);
        RepoMemory::setCrafts($resultItemId, $crafts);
        return $crafts;
    }
}