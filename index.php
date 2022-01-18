<?php
//declare(strict_types=1);
spl_autoload_register(function ($class) {
    require_once __DIR__ . "/{$class}.php";
});

use App\Views\View;

$route = isset($_GET['route']) ? $_GET['route'] : '';
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
    $view = new View(sprintf("%s/templates/", __DIR__));
    $view->setCode(404);
    $view->renderHtml('errors/404.php');
    return;
}
/** @var $matches */
unset($matches[0]);
//unset($_GET['route']);
if (!empty($controllerAndAction)) {
    $controllerName = $controllerAndAction[0];
    $actionName = $controllerAndAction[1];

    $controller = new $controllerName();
    $controller->$actionName(...$matches);
}

//echo "<pre>";
//print_r(get_declared_classes());
//echo "</pre>";