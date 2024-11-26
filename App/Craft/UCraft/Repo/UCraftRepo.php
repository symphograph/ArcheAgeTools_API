<?php

namespace App\Craft\UCraft\Repo;

use App\Craft\UCraft\UCraft;

class UCraftRepo implements RepoITF
{
    static function getBest(int $resultItemId): ?UCraft
    {
        // Сначала проверяем память.
        $uCraft = RepoMemory::getBest($resultItemId);
        if(!empty($uCraft)) return $uCraft;

        $uCraft = RepoDB::getBest($resultItemId);
        if(!empty($uCraft)) {
            RepoMemory::setBest($resultItemId, $uCraft);
        }
        return $uCraft;
    }

    static function byId(int $craftId): ?UCraft
    {
        $uCraft = RepoMemory::byId($craftId);
        if(!empty($uCraft)) return $uCraft;

        $uCraft = RepoDB::byId($craftId);
        if(!empty($uCraft)) {
            RepoMemory::setCraft($uCraft);
        }
        return $uCraft;
    }
}