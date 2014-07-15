<?php
spl_autoload_register(array('Comet', 'autoload'));
abstract class Comet
{
    
    public static $controller = null; # Holds the Comet Controller
    
    public static function buffer($buffer)
    {
        return $buffer;
    }
    
    public static function autoload($class = "")
    {   
    
        $_class = null;
        if (preg_match('/controller/i', $class)) {
            $_class = str_replace('Controller', '', $class);   
        }
        
        $file_path = "";
        $parts = preg_split('/(?=[A-Z])/', ((!empty($_class)) ? $_class : $class));
        foreach ($parts as $part) {
            $file_path .= strtolower($part) . "/";
        }
        $file_path    = str_replace('/', '', substr($file_path, 0, -1) . ".php");
        $include_path = get_include_path() . "/";
        
        if (file_exists("{$include_path}core/{$file_path}")) {
            include_once("{$include_path}core/{$file_path}");
        }           
        
        if (file_exists("{$include_path}models/{$file_path}")) {
            include_once("{$include_path}models/{$file_path}");
        }
        
        if (file_exists("{$include_path}api/{$file_path}")) {
            include_once("{$include_path}api/{$file_path}");
        }
        
        if (file_exists("{$include_path}controllers/{$file_path}")) {
            include_once("{$include_path}controllers/{$file_path}");
        }
        
        if (file_exists("{$include_path}lib/{$file_path}")) {
            include_once("{$include_path}lib/{$file_path}");
        }
        
    }
    
    public static function isAppFileByClass($class = "", $return_path = false)
    {                
        $_class = null;
        if (preg_match('/controller/i', $class)) {
            $_class = str_replace('Controller', '', $class);   
        }
        
        $file_path = "";
        $parts = preg_split('/(?=[A-Z])/', ((!empty($_class)) ? $_class : $class));
        foreach ($parts as $part) {
            $file_path .= strtolower($part) . "/";
        }
        $file_path    = str_replace('/', '', substr($file_path, 0, -1) . ".php");
        $include_path = get_include_path() . "/";
                
        if (file_exists("{$include_path}core/{$file_path}")) {
            return (($return_path) ? "{$include_path}core/{$file_path}" : true);
        }           
        elseif (file_exists("{$include_path}models/{$file_path}")) {
            return (($return_path) ? "{$include_path}models/{$file_path}" : true);
        }
        elseif (file_exists("{$include_path}api/{$file_path}")) {
            return (($return_path) ? "{$include_path}api/{$file_path}" : true);
        }
        elseif (file_exists("{$include_path}controllers/{$file_path}")) {
            return (($return_path) ? "{$include_path}controllers/{$file_path}" : true);
        }
        
        return false;
    }
    
    public static function init()
    {
        ini_set('error_log', dirname(get_include_path()) . "/logs/errors.log");
        include_once(get_include_path() . "/config.php");
        include_once(get_include_path() . "/core/exceptions.php");
        
        ini_set('memory_limit', '-1');
        
        if (defined('SESSION_NAME')) {
            @session_name(SESSION_NAME);
        }
        
        @session_start();
    }
    
    public static function run()
    {
        self::init();
        
        if ($_GET['type'] === 'backend') { # Do security check
            # Security::backend();
        }
        
        if (isset($_GET['view'])) {
            $view_action = $_GET['view'];
        }
        elseif (isset($_GET['action'])) {
            $view_action = $_GET['action'];
        }
        
        $controller = self::loadControllerByName($_GET['controller'], $_GET['type']);
        $method     = self::camalizeMethodName($view_action);
        
        if (method_exists($controller, $method)) {
            $controller->name = $_GET['controller'];
            $controller->view = $view_action;
            $controller->$method();
        }
        
        if ($controller && method_exists($controller, "renderViewByName")) { # Render Version with Local Controller
            $controller->renderViewByName($view_action, $_GET['type']);
        }
        elseif (method_exists(self::$controller, "renderViewByName")) { # Just render view
            self::$controller->renderViewByName($view_action, $_GET['type']);
        }
            
        exit;
    }
    
    public static function loadControllerByName($name = "", $type = "")
    {
        if ($type == 'api') {
            self::$controller = new Api();
            return self::$controller->load($name, $_REQUEST);
        }
        
        self::$controller = new Controller($_REQUEST);
        return self::$controller->load($name);
    }

    public static function camalizeClassName($class_name = "")
    {
        $c  = ucfirst(preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $class_name));
        return ucfirst(preg_replace('/(^|\/)([a-z])/e', 'strtoupper("\\2")', $c));
    }
    
    public static function camalizeMethodName($method_name = "")
    {
        $m = lcfirst(preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $method_name));
        return lcfirst(preg_replace('/(^|\/)([a-z])/e', 'strtoupper("\\2")', $m));
    }
    
    public static function getTableNameFromClass($class_name)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class_name));
    }
    
    public static function getDir($type = "")
    {
        $_type = strtolower($type);
        return "/assets/{$_type}/";
    }
    
    public static function log($msg = "")
    {
        $timestamp = date("Y-m-d H:i:s");
        $_msg      = "[{$timestamp}]: " . $msg . "\n";
        error_log($_msg, 3, dirname(get_include_path()) . "/logs/system.log");
    }
    
    public static function errorLog($msg = "")
    {
        $timestamp = date("Y-m-d H:i:s");
        $_msg      = "[{$timestamp}]: " . $msg . "\n";
        error_log($msg, 3, dirname(get_include_path()) . "/logs/error.log");   
    }
    
    public static function prettyPrint($arr = array())
    {
        echo "<pre>\n";
        print_r($arr) . "\n";
        echo "</pre>\n";  
    }
    
    public static function assertMethod($method = '')
    {
        
        if ($_SERVER['REQUEST_METHOD'] != strtoupper($method)) {
            return false;
        }
        return true;
            
    }
    
    public static function addWarning($msg, $type, $persistant = false)
    {
        self::_addMessage('warnings', $msg, $type, $persistant);
    }
    
    public static function getWarnings($type, $force_clean = false)
    {
        return self::_getMessage('warnings', $type, $force_clean);   
    }
    
    public static function addError($msg, $type, $persistant = false)
    {
        self::_addMessage('errors', $msg, $type, $persistant);
    }
    
    public static function getErrors($type, $force_clean = false)
    {
        return self::_getMessage('errors', $type, $force_clean);   
    }
    
    private static function _addMessage($category, $msg, $type, $persistant)
    {        
        # Build new message
        $new = array(
            'msg'        => $msg,
            'type'       => $type,
            'persistant' => $persistant
        );
        
        # Get existing warnings if there are persistant ones
        $messages = Cache::get($category, 'session');
        if (!$messages) {
            $messages = array();
        }
        
        # Add message to cache
        $messages[] = $new;
        Cache::add($category, $messages, 'session');
    }
    
    private static function _getMessage($category, $type, $force_clean)
    {
        $response = array();
        $messages = Cache::get($category, 'session');
        
        if ($messages) {
            
            foreach ($messages as $k => $message) {
                
                # Find the right type
                if ($message['type'] == $type) {
                    
                    # Add msg to response
                    $response[] = $message['msg'];
                    
                    # If not persistant, destroy after getting message, or if clean forced
                    if (!$message['persistant'] || $force_clean) {
                        unset($messages[$k]);
                    }
                    
                }
                
            }
            
            # Add back new or clear if empty
            if (!empty($messages)) {
                Cache::add($category, $messages, 'session');
            }
            else {
                Cache::clear($category, 'session');
            }
        }
        
        return $response;
    }
}