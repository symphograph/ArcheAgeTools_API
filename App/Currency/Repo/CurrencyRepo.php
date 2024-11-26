<?php

namespace App\Currency\Repo;

class CurrencyRepo implements RepoITF
{
    static function getTradeableIds(int $currencyId): array
    {
        // Сначала проверяем память.
        $ids = RepoMemory::getTradeableIds($currencyId);
        if (!empty($ids)) {
            return $ids;
        }

        /*
        // Если память пустая, проверяем Redis.
        $ids = RepoRedis::getTradeableIds($currencyId);
        if (!empty($ids)) {
            RepoMemory::setTradeableIds($currencyId, $ids); // Кэшируем в память.
            return $ids;
        }
        */

        // Если в Redis пусто, загружаем из базы.
        $ids = RepoDB::getTradeableIds($currencyId);
        if (!empty($ids)) {
            // Кэшируем в Redis и память.
            //RepoRedis::setTradeableIds($currencyId, $ids);
            RepoMemory::setTradeableIds($currencyId, $ids);
        }

        return $ids;
    }

    static function getIds(): array
    {
        $ids = RepoMemory::getIds();
        if (!empty($ids)) return $ids;

        $ids = RepoDB::getIds();
        if (!empty($ids)) {
            RepoMemory::setIds($ids);
        }
        return $ids;
    }
}
