<?php

namespace App\User;

class ServerGroup
{
    public int $id;
    public string $label;
    public array $servers;

    public function initLabel(): void
    {
        $names = array_column($this->servers, 'name');
        $this->label = implode('|',$names);
    }
}