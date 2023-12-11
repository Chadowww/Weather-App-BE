<?php

namespace App\Exceptions;

use Exception;

class ResourceNotFoundException extends Exception
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}