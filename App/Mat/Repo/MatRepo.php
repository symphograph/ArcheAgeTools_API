<?php

namespace App\Mat\Repo;

class MatRepo implements RepoITF
{
    static function listByCraft(int $craftId): array
    {
        $mats = RepoMemory::listByCraft($craftId);
        if(!empty($mats)) return $mats;

        $mats = RepoDB::listByCraft($craftId);
        RepoMemory::setList($craftId, $mats);
        return $mats;
    }

    static function getSolidIds(): array
    {
        $ids = RepoMemory::getSolidIds();
        if(!empty($ids)) return $ids;

        $ids = RepoDB::getSolidIds();
        RepoMemory::setSolidIds($ids);
        return $ids;
    }
}