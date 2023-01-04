<?php

namespace DBServices;

class ItemFixer
{
    public static function craftableCol(): void
    {
        qwe("update items set craftable = 0 where id")
        or die('craftableCol err');
        qwe("
            update items 
            set craftable = 1 
            where id in 
                  (select resultItemId from crafts where onOff)"
        ) or die('craftableCol err');
    }
}