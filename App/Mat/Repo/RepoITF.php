<?php

namespace App\Mat\Repo;

interface RepoITF
{
    static function listByCraft(int $craftId): array;

    static function getSolidIds(): array;
}