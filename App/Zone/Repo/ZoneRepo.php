<?php

namespace App\Zone\Repo;

use App\Zone\Repo\RepoITF;
use App\Zone\Zone;

class ZoneRepo implements RepoITF
{
    static function get(int $id): ?Zone
    {
        $zone = RepoMemory::get($id);
        if(!empty($zone)) return $zone;

        $zones = RepoDB::all();
        RepoMemory::setAll($zones);
        return RepoMemory::get($id);
    }

    /**
     * @return Zone[]
     */
    static function all(): array
    {
        $zones = RepoMemory::all();
        if(!empty($zones)) return $zones;

        $zones = RepoDB::all();
        RepoMemory::setAll($zones);
        return $zones;
    }
}