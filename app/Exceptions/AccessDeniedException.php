<?php

namespace App\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\HTTPExceptionInterface;
use RuntimeException;

class AccessDeniedException extends RuntimeException implements ExceptionInterface, HTTPExceptionInterface
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $code = 403; // Kode status HTTP 403 Forbidden
}