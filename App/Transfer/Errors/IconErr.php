<?php

namespace App\Transfer\Errors;

class IconErr extends TransferErr
{
    protected string $type = 'IconErr';
    public string $logFolder = 'errors/transfer';

    public function __construct(
        string $message = 'icon transfer is err',
        string $pubMsg = 'Ошибка трансфера',
        int $httpStatus = 500
    )
    {
        parent::__construct($message, $pubMsg, $httpStatus);
    }

}