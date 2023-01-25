<?php

namespace App\Test\Try;

interface TryInterface
{
    public static function getMyClass(): self;

    public function getVars();
}