<?php

namespace app\middleware;

use core\route\Middleware;

class PageMiddleware extends Middleware
{
    public function checkAccess()
    {
        return true;
    }
}