<?php

namespace App\Transfer\Errors;

use Symphograph\Bicycle\Helpers;

class ItemErr extends TransferErr
{
    protected string $type = 'ItemErr';
    public string $logFolder = 'errors/transfer';

    public function __construct(
        string $message = 'transfer is err',
        string $pubMsg = 'Ошибка трансфера',
        int $httpStatus = 500
    )
    {
        $this->type = Helpers::classBasename(self::class);
        $msg = $this->type . ': ' . $message;
        parent::__construct($msg, $pubMsg, $httpStatus);
    }
}