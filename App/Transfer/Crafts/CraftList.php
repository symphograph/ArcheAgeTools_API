<?php

namespace App\Transfer\Crafts;

use App\DTO\CraftDTO;


class CraftList extends CraftDTO
{
    public static function errors(int $startId, string $orderBy, ?int $limit = null): array
    {
        $list = CraftTransLog::getErrorList($startId, $orderBy, $limit);
        return array_column($list, 'id');
    }

    public static function errorsFiltered(array $filters, int $startId, string $orderBy, ?int $limit = null): array
    {
        $list = CraftTransLog::getFilteredErrorList($filters, $startId, $orderBy, $limit);
        return array_column($list, 'id');
    }
}