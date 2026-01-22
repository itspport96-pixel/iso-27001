<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\{Request, Response, Session};

final class RoleMiddleware
{
    private array $roles;

    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    public function handle(Request $req, callable $next): mixed
    {
        $userRole = Session::get('rol');
        if (!in_array($userRole, $this->roles, true)) {
            (new Response())->status(403)->text('Forbidden');
        }
        return $next($req);
    }
}
