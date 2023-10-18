<?php

namespace App\Transfer\Errors;

use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Helpers;

class TransferErr extends MyErrors
{
    protected string $type = 'TransferErr';
    public string $logFolder = 'errors/transfer';

    public function __construct(
        string $message = 'transfer is err',
        string $pubMsg = 'Ошибка трансфера',
        int $httpStatus = 500
    )
    {
        $this->type = Helpers::classBasename(self::class);
        $msg = $message;
        parent::__construct($msg, $pubMsg, $httpStatus);
    }
}