<?php

namespace App\Server;

class ServerGroup
{
    public string $label;

    public function __construct(public int $id, public array $servers)
    {
        $this->initLabel();
    }

    public function initLabel(): static
    {
        $names = array_column($this->servers, 'name');
        $this->label = implode(' * ',$names);
        return $this;
    }
}