<?php

/**
 * Origin PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://origin.eppbar.org/license/new-bsd.txt
 *
 * @copyright  Copyright (c) 2011 RiverSing International Ltd. (http://eppbar.org)
 * @license    http://origin.eppbar.org/license/new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\mvc;

/**
 * MVC Router.
 * @author Johnson Tsang <johnson@eppbar.org> 2012-03-16
 */
class Router {
    
    /**
     * actions file extension
     */
    const _ACTION_FILE_EXT = '.php';
    
    /**
     * Bootstrap file name must be Bootstrap.php
     * And class name must be Bootstrap
     */
    const _BOOTSTRAP_CLASS_NAME = '_Bootstrap';
    const _BOOTSTRAP_FILE = '_Bootstrap.php';
    
    /**
     * Default layout view file for render view
     * @var string
     */
    const _DEFAULT_LAYOUT_VIEW_FILE = '_layout';
    
    /**
     * Max URL param count
     */
    private $_max_url_param_count = 10;
    
    /**
     * @var origin\mvc\RoutingInfo
     */
    private $_routing_info;
    
    /**
     * @var string
     */
    private $_action_method_name = 'index';
    
    /**
     * Action root path, format like '/www/project/application/actions'
     * @var string
     */
    private $_action_root_path;
    
    /**
     * Action root namespace, format like 'application\\actions'
     * @var string
     */
    private $_action_root_namespace = '';
    
    /**
     * Is render view after executing action
     * @var boolean
     */
    private $_is_render_view = TRUE;

    /**
     * Action root path, format like '/www/project/application/actions'
     * Action root namespace, format like 'application\\actions'
     * @param string $action_root_path
     * @param string $action_root_namespace
     */
    public function setActionRootPath($action_root_path, $action_root_namespace = '') {
        $this->_action_root_path = $action_root_path;
        $this->_action_root_namespace = $action_root_namespace;
    }

    /**
     * Get Routing Information
     * @return origin\mvc\RoutingInfo
     */
    public function getRoutingInfo() {
        if (empty($this->_routing_info)) {
            throw new \LogicException('Not route to action yet');
        }
        return $this->_routing_info;
    }

    /**
     * Get action method name
     * @return string
     */
    public function getActionMethodName() {
        return $this->_action_method_name;
    }

    /**
     * Set is render view after executing action
     * @param boolean $value
     */
    public function setIsRenderView($value) {
        $this->_is_render_view = $value;
    }

    /**
     * Set max action directory depth for parsing
     * @param int $value
     */
    public function setMaxUrlParamCount($value) {
        $this->_max_url_param_count = $value;
    }

    /**
     * Parse URL information and run related action
     * also run related bootstrap if exists.
     * @param origin\mvc\Controller $controller
     * @param string $allow_types
     * @param callable $callback_404
     */
    public function routeToAction(Controller $controller, $allow_types = FALSE, $callback_404 = FALSE, $action_finder = FALSE) {
        $asi = $this->getActionScriptInfo($allow_types, $action_finder);
        if (empty($asi) || $asi->bootstrap_class_name == $asi->action_class_name) {
            if (is_callable($callback_404)) {
                call_user_func($callback_404);
            } else {
                header("HTTP/1.1 404 Not Found");
                header("Status: 404 Not Found");
            }
            exit();
        }
        $this->_routing_info = $asi;
        $controller->getView()->setViewPath($asi->action_url);
        
        if (is_file($asi->bootstrap_script_path)) {
            $bootstrap = new $asi->bootstrap_class_name();
            $bootstrap->run();
        }
        $action = new $asi->action_class_name();
        $action->setController($controller);
        $asi->action_instance = $action;
        if (method_exists($action, 'onActionCreated')) {
            $action->onActionCreated($asi);
        }
        if (method_exists($action, 'index')) {
            $action->index($asi->action_args);
        }
        if (method_exists($action, 'onActionComplete')) {
            $action->onActionComplete();
        }
        if ($this->_is_render_view) {
            $this->renderView($action, $asi->action_class_name);
        }
    }

    private function renderView(Action $action, $action_class_name) {
        $pos = strrpos($action_class_name, '\\');
        $view_file_name = $pos === FALSE ? $action_class_name : substr($action_class_name, $pos + 1);
        $view_file_name = lcfirst($view_file_name);
        preg_match_all('/[A-Z]/', $view_file_name, $matches, PREG_OFFSET_CAPTURE);
        if (! empty($matches)) {
            $view_file_name = strtolower($view_file_name);
            $matches = array_reverse($matches[0]);
            foreach ($matches as $match) {
                $view_file_name = substr($view_file_name, 0, $match[1]) . '_' . substr($view_file_name, $match[1]);
            }
        }
        if ($this->_action_method_name != 'index') {
            $view_file_name .= '_' . $this->_action_method_name;
        }
        $action->renderView(self::_DEFAULT_LAYOUT_VIEW_FILE, $view_file_name);
    }

    /**
     * Dispatch to the action method when using a parameter for action method name.
     * If $has_default_method is TRUE, $allow_methods first item value will be as the default value.
     * Return action method result.
     * @param array $allow_methods
     * @param bool $has_default_method
     * @throws UnexpectedValueException
     * @return mixed
     */
    public function routeToActionMethod($allow_methods, $has_default_method = TRUE) {
        $asi = $this->getRoutingInfo();
        $action = $asi->action_instance;
        $args = $asi->action_args;
        
        if (! is_object($action)) {
            throw new \UnexpectedValueException('Invalid action object');
        }
        if (empty($args)) {
            $param = FALSE;
        } else {
            if (! is_array($args)) {
                throw new \UnexpectedValueException('Invalid action method parameters');
            }
            $param = $args[0];
            if ($this->isInvalidFunctionName($param)) {
                $param = FALSE;
            } else {
                array_shift($args);
            }
        }
        $method = $this->parseActionMethod($param, $allow_methods, $has_default_method);
        $this->_action_method_name = $method;
        if (empty($args)) {
            return $action->$method();
        } else {
            return $action->$method($args);
        }
    }

    private function parseActionMethod($method, $allow_methods, $has_default_method = TRUE) {
        if (! is_array($allow_methods)) {
            throw new \UnexpectedValueException('Invalid allow_methods');
        }
        
        if (empty($method)) {
            if ($has_default_method) {
                reset($allow_methods);
                return current($allow_methods);
            }
            throw new \UnexpectedValueException('Invalid action method');
        }
        if (in_array($method, $allow_methods)) {
            return $method;
        }
        throw new \UnexpectedValueException('Invalid action method');
    }

    private function stripFileExt($url, $allow_types) {
        if (empty($allow_types)) {
            return $url;
        }
        if (is_string($allow_types)) {
            $allow_types = array($allow_types);
        } else {
            if (! is_array($allow_types)) {
                throw new \UnexpectedValueException('Invalid action allow types');
            }
        }
        $length = strlen($url);
        if ($length <= 1) {
            return $url;
        }
        $pos = strpos($url, '.');
        if ($pos === FALSE) {
            return $url;
        }
        $ext = substr($url, $pos);
        if (in_array($ext, $allow_types)) {
            return substr($url, 0, $pos);
        }
        return FALSE;
    }

    private function retriveUrl($uri) {
        $pos = strpos($uri, '?');
        if ($pos === FALSE) {
            return $uri;
        }
        return substr($uri, 0, $pos);
    }

    /**
     * Get origin URL without querystring before rewrite
     * From Zend Framework ref: Zend/Controller/Request/Http.php setRequestUri()
     * From IIS ref: http://msdn.microsoft.com/en-us/library/ms524602(VS.90).aspx
     * $_SERVER[] name,     rewrite by httpd.conf,     rewrite by .htaccess, rewrite by IIS
     * REDIRECT_URL,        no value,                  before rewrite,       ?
     * SCRIPT_URL,          before rewrite,            no value,             ?
     * SCRIPT_URI,          before rewrite,            no value,             ?
     * REQUEST_URI,         before rewrite,            before rewrite,       before rewrite
     * HTTP_X_REWRITE_URL,  no value,                  no value,             before rewrite
     * UNENCODED_URL,       no value,                  no value,             before rewrite (IIS 5.0 and later)
     * ORIG_PATH_INFO,      before rewrite,            before rewrite,       before rewrite
     * can not use:
     * SCRIPT_NAME,         before rewrite,            current URI,          ?
     * PHP_SELF,            before rewrite,            current URI,          ?
     */
    private function getOriginRequestUrl() {
        if (isset($_SERVER['SCRIPT_URL'])) {
            return $_SERVER['SCRIPT_URL'];
        }
        if (isset($_SERVER['REDIRECT_URL'])) {
            return $_SERVER['REDIRECT_URL'];
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            return $this->retriveUrl($_SERVER['REQUEST_URI']);
        }
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            return $this->retriveUrl($_SERVER['HTTP_X_REWRITE_URL']);
        }
        if (isset($_SERVER['IIS_WasUrlRewritten']) && $_SERVER['IIS_WasUrlRewritten'] == '1' && isset($_SERVER['UNENCODED_URL']) && $_SERVER['UNENCODED_URL'] != '') {
            return $this->retriveUrl($_SERVER['UNENCODED_URL']);
        }
        if (isset($_SERVER['ORIG_PATH_INFO'])) {
            return $_SERVER['ORIG_PATH_INFO'];
        }
        //to here still got no value??? god, let me guess... return '/' ok?
        return '/';
    }

    /**
     * Get action script information.
     * @return RoutingInfo
     */
    private function getActionScriptInfo($allow_types, $action_finder) {
        $url = $this->getOriginRequestUrl();
        $url = $this->stripFileExt($url, $allow_types);
        if ($this->isInvalidActionUrl($url)) {
            return FALSE;
        }
        
        $action_root = $this->_action_root_path;
        if (is_callable($action_finder)) {
            $result = call_user_func($action_finder, $action_root, $url);
            if (! $result) {
                return FALSE;
            }
            
            $action_url = $result['action'];
            if ($this->isInvalidActionUrl($action_url)) {
                return FALSE;
            }
            $pos = strrpos($action_url, '/');
            if ($pos === FALSE) {
                return FALSE;
            }
            $pos ++;
            if ($pos == strlen($action_url)) {
                $info = $this->setupRoutingInfo($action_root, $action_url, 'Index');
            } else {
                $class = substr($action_url, $pos);
                if ($this->isInvalidFunctionName($class)) {
                    return FALSE;
                }
                $info = $this->setupRoutingInfo($action_root, substr($action_url, 0, $pos), ucfirst($class));
            }
            
            $action_args = $result['args'];
            if (is_array($action_args)) {
                $info->action_args = $action_args;
            } elseif (is_string($action_args)) {
                $action_args = explode('/', $result['args']);
                $clean_args = array();
                foreach ($action_args as $one_arg) {
                    $one_arg = trim($one_arg);
                    if (strlen($one_arg) > 0) {
                        $clean_args[] = $one_arg;
                    }
                }
                $info->action_args = $clean_args;
            } else {
                return FALSE;
            }
            
            return $info;
        }
        return $this->defaultActionFinder($action_root, $url);
    }

    /**
     * Default action finder.
     * @return RoutingInfo
     */
    private function defaultActionFinder($action_root, $url) {
        if ($url == '/' || $url == '/index') {
            //visit '/index.php' is special, the file exists, so not rewrited
            //'/' and '/index' accept many visits, deal them for optimizing performance
            $class = 'Index';
            $script = $action_root . '/' . $class . self::_ACTION_FILE_EXT;
            if (is_file($script)) {
                $info = $this->setupRoutingInfo($action_root, '/', 'Index');
                $info->action_args = array();
                return $info;
            }
            return FALSE;
        }
        
        $args = array();
        for ($i = 0; $i < $this->_max_url_param_count; $i ++) {
            $len = strlen($url);
            if ($len <= 1) {
                return FALSE;
            }
            $len --;
            $pos = strrpos($url, '/');
            if ($pos === FALSE) {
                return FALSE;
            }
            if ($pos == $len) { // treat /foo/bar//go/ as /foo/bar/(index)/go/(index)
                $param = FALSE;
                $class = 'Index';
            } else {
                $param = substr($url, $pos + 1);
                if ($this->isInvalidFunctionName($param)) {
                    $url = substr($url, 0, $pos);
                    array_unshift($args, $param);
                    continue;
                }
                $class = ucfirst($param);
            }
            $url = substr($url, 0, $pos);
            $action_url = $url . '/';
            $script = $action_root . $action_url . $class . self::_ACTION_FILE_EXT;
            if (is_file($script)) {
                $info = $this->setupRoutingInfo($action_root, $action_url, $class);
                $info->action_args = $args;
                return $info;
            }
            if ($param) {
                array_unshift($args, $param);
            }
        }
        return FALSE;
    }

    private function setupRoutingInfo($action_root, $action_url, $class) {
        $script_dir = $action_root . $action_url;
        $info = new RoutingInfo();
        $info->action_script_dir = $script_dir;
        $info->action_url = $action_url;
        $action_dir = $this->_action_root_namespace . str_replace('/', '\\', $action_url);
        $info->bootstrap_script_path = $script_dir . self::_BOOTSTRAP_FILE;
        $info->bootstrap_class_name = str_replace('/', '\\', $action_dir . self::_BOOTSTRAP_CLASS_NAME);
        $info->action_class_name = str_replace('/', '\\', $action_dir . $class);
        return $info;
    }

    private function isInvalidActionUrl($url) {
        return ! preg_match('/^[\/0-9a-zA-Z_\-]+$/', $url);
    }

    private function isInvalidFunctionName($name) {
        return ! preg_match('/^[a-z][a-zA-Z0-9_]*$/', $name);
    }
}

?>
