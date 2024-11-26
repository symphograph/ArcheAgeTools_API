<?php

namespace App\Craft\Errors;

class ProfInitErr extends CraftCountErr
{
    public function __construct(int $profId, int $profNeed, int $craftId)
    {
        $msg = "Prof $profId with need $profNeed not found in Craft $craftId";
        parent::__construct($msg);
    }
}