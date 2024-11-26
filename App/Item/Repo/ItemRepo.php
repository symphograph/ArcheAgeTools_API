<?php

namespace App\Item\Repo;

use App\Item\Item;

class ItemRepo implements RepoITF
{
    static function getPrivateIds(): array
    {
        $ids = RepoMemory::getPrivateIds();
        if(!empty($ids)) return $ids;

        $ids = RepoDB::getPrivateIds();
        if(!empty($ids)) {
            RepoMemory::setPrivateIds($ids);
        }
        return $ids;
    }

    static function byId(int $id): Item
    {
        $item = RepoMemory::byId($id);
        if($item) return $item;

        $item = RepoDB::byId($id);
        if($item) RepoMemory::setItem($item);
        return $item;
    }
}