<?php

namespace Test;

interface TryInterface
{
    public static function getMyClass(): self;

    public function getVars();
}