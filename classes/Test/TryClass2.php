<?php

namespace Test;

use Item\Price;

class TryClass2 extends TryClass implements TryInterface
{
    public int $var5 = 5;
    public int $var6 = 6;

    public static function getMyClass(): self
    {
        return new self();
    }

    public function getVars()
    {
        printr([$this->var5,$this->var6]);
    }
}