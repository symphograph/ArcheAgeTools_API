<?php

namespace App\Transfer\Errors;

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
        parent::__construct($message, $pubMsg, $httpStatus);
    }
}