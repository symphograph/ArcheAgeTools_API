<?php

namespace App\Transfer;

class TransferLog
{
    public int     $id;
    public string  $name;
    public string  $error;
    public string  $createdAt;
    public ?string $warnings;


    protected function initWarnings(array $warnings): void
    {
        if(empty($warnings)){
            $this->warnings = '';
            return;
        }
        $this->warnings = implode(' | ', $warnings);
    }

}