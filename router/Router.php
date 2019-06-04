<?php
namespace JSQL;

/**
 * Class Router
 * @package JSQL
 */
class Router
{
    /**
     * @var null Router instance
     */
    private static $instance = null;

    /**
     * @var array Defined routes array
     */
    private $routes = Array();

    /**
     * @var null Path not found error handler
     */
    private $not_found = null;

    /**
     * @var null Method not allowed error handler
     */
    private $not_allowed = null;

    /**
     * Router constructor
     */
    private function __construct()
    {

    }

    /**
     * @return Router|null Lazy router initializer
     */
    public static function getInstance()
    {
        if(!self::$instance)
        {
            self::$instance = new Router();
        }

        return self::$instance;
    }

    /**
     * Set not found error handler
     * @param null $not_found
     * @return Router
     */
    public function setNotFound($not_found)
    {
        $this->not_found = $not_found;
        return $this;
    }

    /**
     * Set not allowed error handler
     * @param null $not_allowed
     * @return Router
     */
    public function setNotAllowed($not_allowed)
    {
        $this->not_allowed = $not_allowed;
        return $this;
    }

    /**
     * Method appends new route to router
     *
     * @param string $expression Expression of url matching
     * @param $function
     * @param string $method Method name string
     */
    private function _add($expression, $function, $method = 'get'){
        array_push($this->routes, Array(
            'expression' => $expression,
            'function' => $function,
            'method' => $method
        ));
    }

    /**
     * Method handler for error 'Path not found'
     * @param $function Callback to error handler
     */
    public static function e_pathNotFound($function){
        Router::getInstance()->setNotFound($function);
    }

    /**
     * Method handler for error 'Method not allowed'
     * @param $function Callback to error handler
     */
    public static function e_methodNotAllowed($function){
        Router::getInstance()->setNotAllowed($function);
    }

    /**
     * Method executes routing handlers
     * @param string $basepath Route destination path
     */
    public function _run($basepath = '/'){

        // Parse current url
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);

        // Check if any paths are present in uri
        $path = $parsed_url['path'] ?? '/';

        // Retrieve server request method
        $method = $_SERVER['REQUEST_METHOD'];

        // Matching flags
        $path_match_found = false;
        $route_match_found = false;

        // Routes check loop
        foreach($this->routes as $route)
        {
            // Appending basepath to uri if does not exist
            if ( $basepath != '' && $basepath != '/' )
            {
                $route['expression'] = '('.$basepath.')'.$route['expression'];
            }

            // Add 'find string start' automatically
            $route['expression'] = '^'.$route['expression'];

            // Add 'find string end' automatically
            $route['expression'] = $route['expression'].'$';

            // echo $route['expression'].'<br/>';

            // Check path match
            if ( preg_match('#'.$route['expression'].'#',$path,$matches) )
            {
                $path_match_found = true;

                if ( strtolower($method) == strtolower($route['method']) )
                {
                    array_shift($matches);

                    if( $basepath != '' && $basepath != '/' )
                    {
                        array_shift($matches);
                    }

                    call_user_func_array($route['function'], $matches);
                    $route_match_found = true;

                    break;
                }
            }
        }

        // No matching route was found
        if ( !$route_match_found )
        {
            if ($path_match_found)
            {
                header("HTTP/1.0 405 Method Not Allowed");
                if($this->not_allowed)
                {
                    call_user_func_array($this->not_allowed, Array($path,$method));
                }
            }
            else
            {
                header("HTTP/1.0 404 Not Found");
                if ($this->not_found)
                {
                    call_user_func_array($this->not_found, Array($path));
                }
            }

        }

    }

    /**
     * Method appends new route to router (WRAPPER)
     *
     * @param string $expression Expression of url matching
     * @param $function
     * @param string $method Method name string
     */
    public static function add($expression, $function, $method = 'get')
    {
        Router::getInstance()->_add($expression, $function, $method);
    }

    /**
     * Method executes routing handlers (WRAPPER)
     * @param string $basepath Route destination path
     */
    public static function run($basepath = '/')
    {
        Router::getInstance()->_run($basepath);
    }
}
