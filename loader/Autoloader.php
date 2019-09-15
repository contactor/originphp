<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\loader;

/**
 * Auto load class.
 * @author Johnson Tsang <contactor@gmail.com> 2013-01-06
 */
class Autoloader {
    private static $_self;
    private $_suppressNotFoundWarnings = FALSE;
    private $_is_registered = FALSE;

    private function __construct() {
    }

    public static function getInstance() {
        if (empty(self::$_self)) {
            self::$_self = new Autoloader();
        }
        return self::$_self;
    }

    public function suppressNotFoundWarnings($suppressNotFoundWarnings = TRUE) {
        $this->_suppressNotFoundWarnings = $suppressNotFoundWarnings;
    }

    /**
     * Register Origin PHP Framwork autoloader
     */
    public function registerAutoloader() {
        if ($this->_is_registered) {
            return;
        }
        $this->_is_registered = TRUE;
        spl_autoload_register(array($this, 'originAutoloader'));
    }

    /**
     * If class is: 'app\path\MyClass', will load file 'app/path/MyClass.php' 
     * @param string $class_name
     */
    public function originAutoloader($class_name) {
        if (strpos($class_name, '\\') === FALSE) {
            return;
        }
        $file_name = str_replace('\\', '/', $class_name);
        $file_name .= '.php';
        $this->_suppressNotFoundWarnings ? @include $file_name : include $file_name;
    }
}

?>