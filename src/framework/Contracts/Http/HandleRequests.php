<?php

namespace Skeletal\Contracts\Http;

use Skeletal;
use Closure;

interface HandleRequests
{

    public function handleRequest(Closure $callback): void;
}
