<?php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class App {
    private $router;
    private $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'autoescape' => 'html',
        ]);

        $this->twig->addGlobal('session', $_SESSION ?? []);
        $this->twig->addGlobal('flash', $_SESSION['flash'] ?? []);
        unset($_SESSION['flash']);

        $this->router = new Router();

        $this->defineRoutes();
    }

    private function defineRoutes() {
        $r = $this->router;

        $r->add('GET', '', 'HomeController@index');
        $r->add('GET', 'login', 'AuthController@showLogin');
        $r->add('POST', 'login', 'AuthController@login');
        $r->add('GET', 'register', 'AuthController@showRegister');
        $r->add('POST', 'register', 'AuthController@register');
        $r->add('GET', 'logout', 'AuthController@logout');
        $r->add('GET', 'events', 'EventController@index');
        $r->add('GET', 'event/(\d+)', 'EventController@show');

        $r->add('GET', 'event/create', 'EventController@create');
        $r->add('POST', 'event/store', 'EventController@store');
        $r->add('POST', 'event/(\d+)/comment', 'CommentController@store');
        $r->add('POST', 'event/(\d+)/register', 'RegistrationController@register');
        $r->add('GET', 'my-events', 'EventController@myEvents');

        $r->add('GET', 'admin', 'AdminController@index');

        $r->add('POST', 'admin/approve/(\d+)', 'AdminController@approve');
        $r->add('POST', 'admin/reject/(\d+)', 'AdminController@reject');

        $r->add('POST', 'admin/role/(\d+)', 'AdminController@role');
        $r->add('POST', 'admin/delete/(\d+)', 'AdminController@delete');

        $r->add('GET', 'moderator', 'ModeratorController@index');
        $r->add('POST', 'moderator/event/approve/(\d+)', 'ModeratorController@approveEvent');
        $r->add('POST', 'moderator/event/reject/(\d+)', 'ModeratorController@rejectEvent');
        $r->add('POST', 'moderator/comment/approve/(\d+)', 'ModeratorController@approveComment');
        $r->add('POST', 'moderator/comment/reject/(\d+)', 'ModeratorController@rejectComment');

        $r->add('GET', 'api/events', 'ApiController@events');

        $r->add('POST', 'newsletter', 'NewsletterController@store');

        $r->add('GET', 'terms', 'StaticController@terms');
        $r->add('GET', 'privacy', 'StaticController@privacy');
    }

    public function run() {
        $url = $_GET['url'] ?? '';
        if (strpos($url, 'admin') === 0
            || strpos($url, 'moderator') === 0
            || in_array($url, ['event/create', 'dashboard', 'my-events'])
        ) {
            if (empty($_SESSION['user'])) {
                $_SESSION['flash']['danger'] = 'Musíš být přihlášen!';
                header('Location: /login');
                exit;
            }
        }

        $this->router->dispatch($this->twig);
    }
}
