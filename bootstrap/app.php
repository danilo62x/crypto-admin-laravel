<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Atrás de um reverse proxy (Caddy/nginx) que termina o TLS: confiar nos
        // cabeçalhos X-Forwarded-* para o Laravel detectar HTTPS e gerar URLs/
        // redirects corretos (necessário pro Filament/Livewire sob subdomínio).
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
