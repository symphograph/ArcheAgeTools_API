<?php

namespace App\Transfer\Items;

use App\Transfer\TransferLog;
use App\Transfer\TransLogTrait;


class ItemTransLog extends TransferLog
{
    use TransLogTrait;
    const tableName = 'transfer_Items';

}