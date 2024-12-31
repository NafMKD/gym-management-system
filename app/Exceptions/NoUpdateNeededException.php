<?php

namespace App\Exceptions;

use Exception;

class NoUpdateNeededException extends Exception
{
    protected $message = 'No update needed.';
}
