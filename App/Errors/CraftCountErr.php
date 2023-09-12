<?php

namespace App\Errors;

use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Helpers;

class CraftCountErr extends MyErrors
{
    protected string $type = 'CraftCountErr';
    protected bool $loggable = true;
    public string $logFolder = 'errors/craft';

    public function __construct(
        string $message = 'craft is err',
        string $pubMsg = 'Ошибка при расчете крафта',
        int $httpStatus = 500
    )
    {
        $this->type = Helpers::classBasename(self::class);
        $msg = $message;
        parent::__construct($msg, $pubMsg, $httpStatus);
    }
}