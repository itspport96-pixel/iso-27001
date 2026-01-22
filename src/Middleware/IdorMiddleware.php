<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\{Request, Response, TenantContext};

final class IdorMiddleware
{
    public function handle(Request $req, callable $next): mixed
    {
        $routeId = (int)$req->input('id');
        if ($routeId > 0) {
            $tenantId = TenantContext::getTenant();
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT 1 FROM soa_entries WHERE id = :id AND empresa_id = :empresa_id LIMIT 1');
            $stmt->execute(['id' => $routeId, 'empresa_id' => $tenantId]);
            if (!$stmt->fetch()) {
                (new Response())->status(403)->json(['error' => 'Resource does not belong to your tenant']);
            }
        }
        return $next($req);
    }
}
