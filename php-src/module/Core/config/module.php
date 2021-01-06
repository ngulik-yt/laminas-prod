<?php
declare (strict_types = 1);

namespace Core;

use Laminas\Stdlib\ArrayUtils;

$routes = [];
foreach (glob(__DIR__ . '/route/*.route.php') as $filename) {
    $routes = ArrayUtils::merge($routes, include $filename);
}
// !d($routes);die();

$console_routes = [];
foreach (glob(__DIR__ . '/route/*.console.php') as $filename) {
    $console_routes = ArrayUtils::merge($console_routes, include $filename);
}

return [
    'modules' => ["Core" => ["session_name" => null], "App" => ["session_name" => "App"]],
    'router' => [
        'routes' => $routes,
    ],
    'console' => [
        'router' => [
            'routes' => $console_routes,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'layout' => 'layout/blank',
        'not_found_template' => 'error/404-A',
        'exception_template' => 'error/1',
        'default_template_suffix' => 'phtml',
        'template_map' => [
            'layout/blank' => APP_PATH . '/views/templates/layout/blank.phtml',
            'layout/layout' => APP_PATH . '/views/templates/layout/layout.phtml',
            'layout/admin_lte' => APP_PATH . '/views/templates/layout/admin_lte.phtml',
            'layout/admin_lte_blank' => APP_PATH . '/views/templates/layout/admin_lte_blank.phtml',
            'layout/admin_lte_top' => APP_PATH . '/views/templates/layout/admin_lte_top.phtml',
            'layout/dark_admin' => APP_PATH . '/views/templates/layout/dark_admin.phtml',

            'error/404' => APP_PATH . '/views/pages/error/404-A.phtml',
            'error/error' => APP_PATH . '/views/pages/error/1.phtml',
        ],
        'template_path_stack' => [
            "View" => APP_PATH . '/views',
            "View/Page" => APP_PATH . '/views/pages',
            "View/Page/Error" => APP_PATH . '/views/pages/error',
            "View/Page/Login" => APP_PATH . '/views/pages/login',
            "View/Template" => APP_PATH . '/views/templates',
            "View/Template/Layout" => APP_PATH . '/views/templates/layout',
            "View/Template/Email" => APP_PATH . '/views/templates/email',
            "Core" => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy', // register JSON renderer strategy
            'ViewFeedStrategy', // register Feed renderer strategy
        ],
    ],
];