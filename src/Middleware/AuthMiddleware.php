<?

namespace Design\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;

class AuthMiddleware
{
    public function handle(ServerRequestInterface $request, \Closure $next)
    {
        if (!!($_SESSION['user_id'] ?? false)) {
            return $next($request);
        }

        return new RedirectResponse('/');
    }

    public function isAuthorized(ServerRequestInterface $request, \Closure $next)
    {
        if (!!($_SESSION['user_id'] ?? false)) {
            return new RedirectResponse('/profile');
        }
    }
}
