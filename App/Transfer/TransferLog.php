<?php

namespace App\Transfer;

abstract class TransferLog
{
    public int     $id;
    public string  $name;
    public ?string  $error;
    public string  $createdAt;
    public ?string $warnings;
    public string $status;

    public function initWarnings(array $warnings): void
    {
        if(empty($warnings)){
            $this->warnings = '';
            return;
        }
        $this->warnings = implode(' | ', $warnings);
    }

    public static function newInstance(int $id, string $name, ?string $error = null, array $warnings = []): static
    {
        $Log = new static();
        $Log->id = $id;
        $Log->name = $name;
        $Log->error = $error;
        $Log->createdAt = date('Y-m-d H:i:s');
        $Log->initWarnings($warnings);
        $Log->status = TransferStatus::Process->value;
        return $Log;
    }

    public function setStatus(TransferStatus $status): void
    {
        $this->status = $status->value;
    }

    public function setError(string $error): void
    {
        $this->error = $error;
        $this->setStatus(TransferStatus::Error);
    }
}