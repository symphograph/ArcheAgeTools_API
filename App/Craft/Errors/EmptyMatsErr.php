<?php

namespace App\Craft\Errors;

class EmptyMatsErr extends CraftCountErr
{
    public function __construct(int $craftId)
    {
        $msg = "Craft $craftId has empty materials";
        parent::__construct($msg, "Материалы не найдены");
    }
}