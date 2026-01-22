<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\{Request, Response, Session, TenantContext};

final class TenantMiddleware
{
    public function handle(Request $req, callable $next): mixed
    {
        $empresaId = Session::get('empresa_id');
        if (!is_int($empresaId)) {
            (new Response())->status(403)->json(['error' => 'Tenant not found in session']);
        }
        TenantContext::setTenant($empresaId);
        return $next($req);
    }
}
