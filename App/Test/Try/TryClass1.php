<?php

namespace App\Test\Try;

class TryClass1 extends TryClass implements TryInterface
{
    public int $var3 = 3;
    public int $var4 = 4;

    public static function getMyClass(): self
    {
        return new self();
    }

    public function getVars()
    {
        printr([$this->var3,$this->var4]);
    }
}