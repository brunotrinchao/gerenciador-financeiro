<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * Confia em todos os proxies (como os da Vercel).
     */
    protected $proxies = '*';

    /**
     * Habilita os cabeçalhos como X-Forwarded-Proto.
     */
    protected $headers = Request::HEADER_FORWARDED;
}
