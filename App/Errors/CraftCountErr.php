<?php

namespace App\Errors;

class CraftCountErr extends MyErrors
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}