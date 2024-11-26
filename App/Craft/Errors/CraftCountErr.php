<?php

namespace App\Craft\Errors;

use Symphograph\Bicycle\Errors\MyErrors;

class CraftCountErr extends MyErrors
{
    public bool      $loggable  = true;
    public string    $logFolder = 'errors/craft';

    public function __construct(
        string $message = 'craft is err',
        string $pubMsg = 'Ошибка при расчете крафта'
    )
    {
        parent::__construct($message, $pubMsg);
    }
}