<?php

namespace App\Transfer;

use PDO;

abstract class TransferAgent
{
    protected array  $errorFilter = [];

    public function __construct
    (protected TransParams $params)
    {
        if(!$params->startId){
            $this->params->startId = $this->getLast();
        }
    }

}