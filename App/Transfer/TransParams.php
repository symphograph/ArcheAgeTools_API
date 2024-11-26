<?php

namespace App\Transfer;

class TransParams
{
    public ?string $orderBy = null;

    /**
     * @param int $startId [optional]
     * <p>Sets first itemId in list</p>
     * <p>If null List will be started from last imported item</p>
     * <p>If you want get allList, set to 1</p>
     * @param int $limit
     * @param bool $readOnly
     * @param bool $random
     */
    public function __construct(
        public ?int  $startId = null,
        public ?int  $limit = null,
        public bool $readOnly = true,
        bool $random = false
    )
    {
        if($random){
            $this->orderBy = 'rand()';
            $this->startId = 1;
        }
    }
}