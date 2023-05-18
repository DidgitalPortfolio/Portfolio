<?
require __DIR__ . '/../src/bootstrap.php';

use MiladRahimi\PhpRouter\Router;

use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
// use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Design\Controllers\IndexController;
use Design\Controllers\AuthController;
use Design\Controllers\ActionController;
use Design\Middleware\AuthMiddleware;
use Design\Middleware\IsAuthorizedMiddleware;

session_start();

$router = Router::create();
$router->setupView('../src/Views');
$router->pattern('id', '[0-9]+');
$router->pattern('name', "[a-zA-Z0-9()%-]+");

$router->get('/404', [IndexController::class, 'code404']);

$router->group(['middleware' => [IsAuthorizedMiddleware::class]], function (Router $router) {
    $router->get('/', IndexController::class);
});

$router->post('/action/auth', [AuthController::class, 'auth']);
$router->post('/action/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->group(['middleware' => [AuthMiddleware::class]], function (Router $router) {
    $router->get('/profile', [IndexController::class, 'profile']);
    $router->get('/profile/{id}', [IndexController::class, 'profileUser']);
    $router->get('/main', [IndexController::class, 'main']);
    $router->get('/liked', [IndexController::class, 'liked']);
    $router->get('/project/{id}', [ActionController::class, 'getProject']);
    $router->post('/action/project/create', [ActionController::class, 'createProject']);
    $router->get('/action/project', [ActionController::class, 'getProfileProjects']);
    $router->post('/action/image/upload', [ActionController::class, 'imageUpload']);
    $router->post('/action/avatar/upload', [ActionController::class, 'avatarUpload']);
    $router->post('/action/comment', [ActionController::class, 'createComment']);
    $router->post('/action/like', [ActionController::class, 'createLike']);
    $router->post('/action/socials', [ActionController::class, 'updateSocials']);
});


try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->getPublisher()->publish(new RedirectResponse('/404'));
}
