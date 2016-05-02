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
 * MVC Controller. I am thin!
 * @author Johnson Tsang <johnson@eppbar.org> 2011-03-12
 */
class Controller {
    
    /**
     * Router Object
     * @var Router
     */
    private $_router;
    
    /**
     * View Object
     * @var View
     */
    private $_view;

    public function __construct() {
        $this->_router = new Router();
        $this->_view = new View();
    }

    /**
     * Get Router object
     * @return origin\mvc\Router
     */
    public function getRouter() {
        return $this->_router;
    }

    /**
     * Get View object
     * @return origin\mvc\View
     */
    public function getView() {
        return $this->_view;
    }

    /**
     * Get Action instance object
     * @return origin\mvc\Action
     */
    public function getAction() {
        return $this->getRouter()->getRoutingInfo()->action_instance;
    }

    /**
     * Parse URL information and run related action
     * also run related bootstrap if exists.
     * @param string $allow_types
     * @param callable $callback_404
     */
    public function runAction($allow_types = FALSE, $callback_404 = FALSE, $action_finder = FALSE) {
        $this->getRouter()->routeToAction($this, $allow_types, $callback_404, $action_finder);
    }
}

?>