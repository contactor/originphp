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
namespace origin\loader;

/**
 * Auto load class.
 * @author Johnson Tsang <johnson@eppbar.org> 2013-01-06
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