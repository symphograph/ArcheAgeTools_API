<?php

namespace App\Errors;

class AccountErr extends MyErrors
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}