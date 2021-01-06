<?php
declare (strict_types = 1);
namespace Core;

use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Stdlib\ArrayUtils;

class Module
{
    /**
     * init function
     *
     * # Load Every Request (1)
     * @param ModuleManager $moduleManager
     */
    public function init(ModuleManager $moduleManager)
    {
        $moduleEvent = $moduleManager->getEventManager();
        //# attach MergeConfig Listener
        $moduleEvent->attach(ModuleEvent::EVENT_MERGE_CONFIG, [$this, 'onMergeConfig']);
    }

    /**
     * getConfig function
     *
     * # Load Every Request (2)
     * @return array
     */
    public function getConfig(): array
    {
        //# load config
        /** @var array $config */
        $config = include __DIR__ . '/../config/module.php';
        // !d($config);die();
        return $config;
    }

    /**
     * onMergeConfig function
     *
     * # Load Every Request (3)
     * @param ModuleEvent $moduleEvent
     */
    public function onMergeConfig(ModuleEvent $moduleEvent)
    {
        $configListener = $moduleEvent->getConfigListener();
        $config = $configListener->getMergedConfig(false);
        //INFO append router config
        $routes = $config['router']['routes'] ??[];
        $config['router']['routes'] = $this->appendRoutes($routes);
        // !d($config['router']['routes']);die();
        //INFO append controller config
        $controllers = $config['controllers']['factories'] ??[];
        $config['controllers']['factories'] = $this->appendControllers($controllers);
        // !d($config['controllers']['factories']);die();
        //INFO append module config
        $modules = $config['modules'] ??[];
        $config['modules'] = $this->appendModules($modules);
        // !d($config['modules']);die();
        $configListener->setMergedConfig($config);
    }

    /**
     * appendRoutes function
     *
     * @param array $routes
     * @return array
     */
    private function appendRoutes(&$routes)
    {
        /**
         * @var boolean $fromcache
         * # set routecache GET param to 0 to reload cache route
         */
        $from_cache = $_GET['routecache'] ?? "1" === "1";
        $from_cache = $_GET['dbcache'] ?? $from_cache === "1";

        /**
         * @var array $tmp_routes
         * # routes data
         */
        $tmp_routes = [];
        $routes_loaded = false;
        $routecache_file = "all_active_route";

        if ($from_cache) { //# load route from cache
            if (check_json_cache($routecache_file)) { //# check route cache exist
                $tmp_routes = load_json_cache($routecache_file);
                $routes_loaded = true;
            }
        }

        if (!$routes_loaded) { //# load route from db
            /**
             * @var \Medoo\Medoo $medooDb
             */
            $medooDb = new \Medoo\Medoo([
                'database_type' => 'mysql',
                'database_name' => _DBADMIN_NAME_,
                'server' => _DBADMIN_HOST_,
                'username' => _DBADMIN_USER_,
                'password' => _DBADMIN_PASSWORD_,
                'port' => _DBADMIN_PORT_,
            ]);

            /**
             * @var array $route
             * # select routes data from db
             */
            $route = $medooDb->query("call get_".$routecache_file."()", [])->fetchAll();
            // !d($route);die();
            $tmp_routes = $this->parseRouteData($route);
            save_json_cache($tmp_routes,$routecache_file);
        }
        // !d($tmp_routes);die();
        return ArrayUtils::merge($routes, $tmp_routes);
    }

    /**
     * parseRouteData function
     *
     * @param array $data
     * @return array $tmp_routes
     */
    private function parseRouteData(&$data)
    {
        $tmp_route = $data;
        $tmp_route2 = [];
        $tmp_routes2 = [];
        $tmp_routes = [];
        // !d($tmp_route);die();
        if (count($tmp_route) > 0) {
            foreach ($tmp_route as $k => $v) {
                $tmp_routes2[$v['id']] = $v;
                if (($v['parent_name'] === null || $v['parent_name'] === '') && !isset($tmp_routes[$v['name']])) {
                    $v['method'] = $v['method'] === null ? "[]" : $v['method'];
                    $tmpmethod = json_decode(strtoupper($v['method']), true);
                    $tmp_routes[$v['name']] = [
                        'type' => ($v['type'] === '' || $v['type'] === null) ? 'literal' : $v['type'],
                        'may_terminate' => $v['may_terminate'] === '0' ? false : true,
                        'options' => [
                            'route' => $v['route'] ?? '',
                            'defaults' => [
                                'id' => $v['id'],
                                'title' => $v['title'] ?? '',
                                'session_name' => $v['session_name'] ?? '',
                                'layout' => $v['layout_name'] ?? _DEFAULT_THEME_,
                                'method' => is_array($tmpmethod) ? $tmpmethod : [],
                                'show_title' => $v['show_title'] === '1' ? true : false,
                                'is_logging' => $v['is_logging'] === '1' ? true : false,
                                'is_caching' => $v['is_caching'] === '1' ? true : false,
                                'is_public' => $v['is_public'] === '1' ? true : false,
                                'is_guest' => $v['is_public'] === '2' ? true : false,
                            ],
                        ],
                        'child_routes' => [],
                    ];
                    if ($v['action'] !== '' && $v['action'] !== null) {
                        $tmp = [
                            $v['module_name'],
                            'Controller',
                            $v['control_name'] . 'Controller',
                        ];
                        $tmp_routes[$v['name']]['options']['defaults']['controller'] = implode("\\", $tmp);
                        $tmp_routes[$v['name']]['options']['defaults']['action'] = $v['act_name'];
                    }
                } else {
                    $tmp_route2[$v['id']] = $v;
                }
            }
        }
        $tmp_route = $tmp_route2;
        foreach ($tmp_route as $k => $v) {
            if (isset($tmp_routes[$v['parent_name']])
                && !isset($tmp_routes[$v['parent_name']]['child_routes'][$v['name']])) {
                $v['method'] = $v['method'] === null ? "[]" : $v['method'];
                $tmpmethod = json_decode(strtoupper($v['method']), true);
                $tmp_routes[$v['parent_name']]['child_routes'][$v['name']] = [
                    'type' => ($v['type'] === '' || $v['type'] === null) ? 'literal' : $v['type'],
                    'may_terminate' => $v['may_terminate'] === '0' ? false : true,
                    'options' => [
                        'route' => $v['route'] ?? '',
                        'defaults' => [
                            'id' => $v['id'],
                            'title' => $v['title'] ?? '',
                            'session_name' => $v['session_name'] ?? '',
                            'layout' => $v['layout_name'] ?? _DEFAULT_THEME_,
                            'method' => is_array($tmpmethod) ? $tmpmethod : [],
                            'show_title' => $v['show_title'] === '1' ? true : false,
                            'is_logging' => $v['is_logging'] === '1' ? true : false,
                            'is_caching' => $v['is_caching'] === '1' ? true : false,
                            'is_public' => $v['is_public'] === '1' ? true : false,
                            'is_guest' => $v['is_public'] === '2' ? true : false,
                        ],
                    ],
                    'child_routes' => [],
                ];
                if ($v['action'] !== '' && $v['action'] !== null) {
                    $tmp = [
                        $v['module_name'],
                        'Controller',
                        $v['control_name'] . 'Controller',
                    ];
                    $tmp_routes[$v['parent_name']]['child_routes'][$v['name']]['options']['defaults']['controller'] = implode("\\", $tmp);
                    $tmp_routes[$v['parent_name']]['child_routes'][$v['name']]['options']['defaults']['action'] = $v['act_name'];
                }
                unset($tmp_route2[$k]);
            }
        }
        $tmp_route = $tmp_route2;
        foreach ($tmp_route as $k => $v) {
            $tmp = [];
            if (isset($tmp_routes2[$v['parent']])) {
                $tmp = $tmp_routes2[$v['parent']];
            }
            if (count($tmp) > 0
                && isset($tmp_routes[$tmp['parent_name']])
                && isset($tmp_routes[$tmp['parent_name']]['child_routes'][$tmp['name']])
                && !isset($tmp_routes[$tmp['parent_name']]['child_routes'][$tmp['name']]['child_routes'][$v['name']])) {
                $v['method'] = $v['method'] === null ? "[]" : $v['method'];
                $tmpmethod = json_decode(strtoupper($v['method']), true);
                $tmp_routes[$tmp['parent_name']]['child_routes'][$tmp['name']]['child_routes'][$v['name']] = [
                    'type' => ($v['type'] === '' || $v['type'] === null) ? 'literal' : $v['type'],
                    'may_terminate' => $v['may_terminate'] === '0' ? false : true,
                    'options' => [
                        'route' => $v['route'] ?? '',
                        'defaults' => [
                            'id' => $v['id'],
                            'title' => $v['title'] ?? '',
                            'session_name' => $v['session_name'] ?? '',
                            'layout' => $v['layout_name'] ?? _DEFAULT_THEME_,
                            'method' => is_array($tmpmethod) ? $tmpmethod : [],
                            'show_title' => $v['show_title'] === '1' ? true : false,
                            'is_logging' => $v['is_logging'] === '1' ? true : false,
                            'is_caching' => $v['is_caching'] === '1' ? true : false,
                            'is_public' => $v['is_public'] === '1' ? true : false,
                            'is_guest' => $v['is_public'] === '2' ? true : false,
                        ],
                    ],
                    'child_routes' => [],
                ];
                if ($v['action'] !== '' && $v['action'] !== null) {
                    $tmpx = [
                        $v['module_name'],
                        'Controller',
                        $v['control_name'] . 'Controller',
                    ];
                    $tmp_routes[$tmp['parent_name']]['child_routes'][$tmp['name']]['child_routes'][$v['name']]['options']['defaults']['controller'] = implode("\\", $tmpx);
                    $tmp_routes[$tmp['parent_name']]['child_routes'][$tmp['name']]['child_routes'][$v['name']]['options']['defaults']['action'] = $v['act_name'];
                }
                unset($tmp_route2[$k]);
            }
        }
        $tmp_route = $tmp_route2;
        foreach ($tmp_route as $k => $v) {
            $tmp = [];
            if (isset($tmp_routes2[$v['parent']])) {
                $tmp = $tmp_routes2[$v['parent']];
            }
            if (isset($tmp_routes2[$tmp['parent']])) {
                $tmp2 = $tmp_routes2[$tmp['parent']];
            }
            if (count($tmp) > 0 && count($tmp2) > 0
                && isset($tmp_routes[$tmp2['parent_name']])
                && isset($tmp_routes[$tmp2['parent_name']]['child_routes'][$tmp2['name']])
                && isset($tmp_routes[$tmp2['parent_name']]['child_routes'][$tmp2['name']])
                && isset($tmp_routes[$tmp2['parent_name']]['child_routes'][$tmp2['name']]['child_routes'][$tmp['name']])
                && !isset($tmp_routes[$tmp2['parent_name']]['child_routes'][$tmp2['name']]['child_routes'][$tmp['name']]['child_routes'][$v['name']])) {
                $v['method'] = $v['method'] === null ? "[]" : $v['method'];
                $tmpmethod = json_decode(strtoupper($v['method']), true);
                $tmp_routes[$tmp2['parent_name']]['child_routes'][$tmp2['name']]['child_routes'][$tmp['name']]['child_routes'][$v['name']] = [
                    'type' => ($v['type'] === '' || $v['type'] === null) ? 'literal' : $v['type'],
                    'may_terminate' => $v['may_terminate'] === '0' ? false : true,
                    'options' => [
                        'route' => $v['route'] ?? '',
                        'defaults' => [
                            'id' => $v['id'],
                            'title' => $v['title'] ?? '',
                            'session_name' => $v['session_name'] ?? '',
                            'layout' => $v['layout_name'] ?? _DEFAULT_THEME_,
                            'method' => is_array($tmpmethod) ? $tmpmethod : [],
                            'show_title' => $v['show_title'] === '1' ? true : false,
                            'is_logging' => $v['is_logging'] === '1' ? true : false,
                            'is_caching' => $v['is_caching'] === '1' ? true : false,
                            'is_public' => $v['is_public'] === '1' ? true : false,
                            'is_guest' => $v['is_public'] === '2' ? true : false,
                        ],
                    ],
                    'child_routes' => [],
                ];
                if ($v['action'] !== '' && $v['action'] !== null) {
                    $tmpx = [
                        $v['module_name'],
                        'Controller',
                        $v['control_name'] . 'Controller',
                    ];
                    $tmp_routes[$tmp2['parent_name']]['child_routes'][$tmp2['name']]['child_routes'][$tmp['name']]['child_routes'][$v['name']]['options']['defaults']['controller'] = implode("\\", $tmpx);
                    $tmp_routes[$tmp2['parent_name']]['child_routes'][$tmp2['name']]['child_routes'][$tmp['name']]['child_routes'][$v['name']]['options']['defaults']['action'] = $v['act_name'];
                }
                unset($tmp_route2[$k]);
            }
        }

        return $tmp_routes;
    }

    /**
     * appendControllers function
     *
     * @param array $controllers
     * @return array
     */
    private function appendControllers(&$controllers)
    {
        /**
         * @var boolean $fromcache
         * # set routecache GET param to 0 to reload cache route
         */
        $from_cache = $_GET['controllercache'] ?? "1" === "1";
        $from_cache = $_GET['dbcache'] ?? $from_cache === "1";

        /**
         * @var array $tmp_controllers
         * # routes data
         */
        $tmp_controllers = [];
        $controllers_loaded = false;
        $controllercache_file = "all_active_script";

        if ($from_cache) { //# load controller from cache
            if (check_json_cache($controllercache_file)) { //# check controller cache exist
                $tmp_controllers = load_json_cache($controllercache_file);
                $controllers_loaded = true;
            }
        }

        if (!$controllers_loaded) { //# load controller from db
            /**
             * @var \Medoo\Medoo $medooDb
             */
            $medooDb = new \Medoo\Medoo([
                'database_type' => 'mysql',
                'database_name' => _DBADMIN_NAME_,
                'server' => _DBADMIN_HOST_,
                'username' => _DBADMIN_USER_,
                'password' => _DBADMIN_PASSWORD_,
                'port' => _DBADMIN_PORT_,
            ]);

            /**
             * @var array $controller
             * # select controller data from db
             */
            $controller = $medooDb->query("call get_".$controllercache_file."()", [])->fetchAll();
            // !d($controller);die();
            foreach ($controller as $v) {
                $tmp = [
                    $v['module_name'],
                    'Controller',
                    $v['control_name'] . 'Controller',
                ];
                $tmp_controllers[implode("\\", $tmp)] = $v['control_factory'];
            }
            save_json_cache($tmp_controllers,$controllercache_file);
        }
        // !d($tmp_controllers);die();
        return ArrayUtils::merge($controllers, $tmp_controllers);
    }

    /**
     * appendModules function
     *
     * @param array $modules
     * @return array
     */
    private function appendModules(&$modules)
    {
        /**
         * @var boolean $fromcache
         * # set routecache GET param to 0 to reload cache route
         */
        $from_cache = $_GET['modulecache'] ?? "1" === "1";
        $from_cache = $_GET['dbcache'] ?? $from_cache === "1";

        /**
         * @var array $tmp_modules
         * # routes data
         */
        $tmp_modules = [];
        $modules_loaded = false;
        $modulecache_file = "all_active_module";

        if ($from_cache) { //# load module from cache
            if (check_json_cache($modulecache_file)) { //# check module cache exist
                $tmp_modules = load_json_cache($modulecache_file);
                $modules_loaded = true;
            }
        }

        if (!$modules_loaded) { //# load module from db
            /**
             * @var \Medoo\Medoo $medooDb
             */
            $medooDb = new \Medoo\Medoo([
                'database_type' => 'mysql',
                'database_name' => _DBADMIN_NAME_,
                'server' => _DBADMIN_HOST_,
                'username' => _DBADMIN_USER_,
                'password' => _DBADMIN_PASSWORD_,
                'port' => _DBADMIN_PORT_,
            ]);

            /**
             * @var array $module
             * # select module data from db
             */
            $module = $medooDb->query("call get_".$modulecache_file."()", [])->fetchAll();
            // !d($controller);die();
            foreach ($module as $v) {
                $tmp_modules[$v['name']]['session_name'] = $v['session_name'];
            }
            save_json_cache($tmp_modules,$modulecache_file);
        }
        // !d($tmp_controllers);die();
        return ArrayUtils::merge($modules, $tmp_modules);
    }
}
