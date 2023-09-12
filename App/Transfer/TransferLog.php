<?php

namespace App\Transfer;

use App\DTO\DTO;
use Symphograph\Bicycle\DTO\DTOTrait;

class TransferLog extends DTO
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