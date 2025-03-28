<?php

declare(strict_types=1);

namespace PermissionsModule\Middleware;

use PermissionsModule\Service\PermissionService;
use PermissionsModule\Value\Permission;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpForbiddenException;

class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly PermissionService $service,
        private readonly Permission $requiredPermission
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userId = (int) $request->getAttribute('userId');

        if ($userId === null) {
            throw new HttpForbiddenException($request, 'Authorization required');
        }

        if (!$this->service->userHasPermission($userId, $this->requiredPermission)) {
            throw new HttpForbiddenException($request, 'Insufficient permissions');
        }

        return $handler->handle($request);
    }
}
