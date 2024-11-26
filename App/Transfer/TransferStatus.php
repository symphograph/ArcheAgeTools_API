<?php

namespace App\Transfer;

enum TransferStatus: string
{
    case Completed = 'completed';
    case Process = 'process';
    case Error = 'error';
}
