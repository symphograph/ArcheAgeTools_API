<?php

namespace App\Transfer\Items;

use App\Transfer\TransferLog;
use Symphograph\Bicycle\DTO\DTOTrait;
use Symphograph\Bicycle\PDO\DB;

class ItemTransLogDTO extends TransferLog
{
    use DTOTrait;
    const string tableName = 'transfer_Items';

    public static function last(): ?static
    {
        $sql = "select * from transfer_Items order by createdAt desc limit 1";
        return DB::qwe($sql)->fetchObject(static::class) ?: null;
    }


}