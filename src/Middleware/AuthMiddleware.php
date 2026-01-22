<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\{Request, Response, Session};

final class AuthMiddleware
{
    public function handle(Request $req, callable $next): mixed
    {
        if (!Session::get('user_id')) {
            (new Response())->redirect('/login');
        }
        return $next($req);
    }
}
