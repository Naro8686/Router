<?php
//declare(strict_types=1);
spl_autoload_register(function (string $class) {
    require_once __DIR__ . "/{$class}.php";
});

use App\Views\View;

$route = $_GET['route'] ?? '';
$routes = require __DIR__ . '/App/routes.php';

$isRouteFound = false;
foreach ($routes as $pattern => $controllerAndAction) {
    preg_match($pattern, $route, $matches);
    if (!empty($matches)) {
        $isRouteFound = true;
        break;
    }
}

if (!$isRouteFound) {
    $view = new View(__DIR__ . '/templates/');
    $view->setCode(404);
    $view->renderHtml('errors/404.php');
    return;
}
/** @var $matches */
unset($matches[0]);
//unset($_GET['route']);
$controllerName = $controllerAndAction[0];
$actionName = $controllerAndAction[1];

$controller = new $controllerName();
$controller->$actionName(...$matches);
//echo "<pre>";
//print_r(get_declared_classes());
//echo "</pre>";