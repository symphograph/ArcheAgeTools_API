<?php

namespace App\Transfer\Crafts;

use App\Transfer\TransferLog;
use App\Transfer\TransLogTrait;
use Symphograph\Bicycle\DTO\DTOTrait;

class CraftTransLog extends TransferLog
{
    use DTOTrait;
    use TransLogTrait;
    const tableName = 'transfer_Crafts';

}