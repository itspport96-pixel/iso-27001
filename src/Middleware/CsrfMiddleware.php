<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\{Request, Response, Session};

final class CsrfMiddleware
{
    public function handle(Request $req, callable $next): mixed
    {
        error_log("CSRF debug - POST: " . json_encode($req->input()) . " SESSION token: " . (Session::token() ?? "null"));
        error_log("CSRF debug - POST: " . json_encode($req->input()) . " SESSION token: " . (Session::token() ?? "null"));
        if (in_array($req->method(), ['POST', 'PUT', 'DELETE'], true)) {
            $token = $req->input(Session::token());
            if (!is_string($token) || !hash_equals(Session::token(), $token)) {
                (new Response())->status(403)->json(['error' => 'CSRF token mismatch']);
            }
        }
        return $next($req);
    }
}
