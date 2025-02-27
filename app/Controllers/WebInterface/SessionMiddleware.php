<?php

namespace App\Controllers\WebInterface;

use Flytachi\Kernel\Src\Factory\Middleware\MiddlewareException;
use Flytachi\Kernel\Src\Stereotype\Middleware;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class SessionMiddleware extends Middleware
{
    public function optionBefore(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!$_SESSION['ACCESS']) {
            throw new MiddlewareException('Authentication Failed');
        }
    }
}
