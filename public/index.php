<?php
if (!isset($_GET['url'])) {
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $script_name = $_SERVER['SCRIPT_NAME'];
    $url = substr($request_uri, strlen(dirname($script_name)));
    if ($url === false || $url === '') $url = '/';
    $url = trim($url, '/');

    $_GET['url'] = $url;
    $_REQUEST['url'] = $url;
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/App.php';

use App\Core\App;

session_start();
$app = new App();
$app->run();
