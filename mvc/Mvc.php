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

use origin\loader\Autoloader;
use origin\form\FormPlus;

/**
 * Application entrance.
 * @author Johnson Tsang <johnson@eppbar.org> 2013-01-07, updated 2014-09-28
 */
class Mvc {
    private static $_self;
    
    /**
     * @var Controller
     */
    private $_controller;
    
    /**
     * @var Action
     */
    private $_action;
    
    /**
     * @var View
     */
    private $_view;
    
    /**
     * FormPlus object
     * @return \origin\form\FormPlus
     */
    private $_form;

    private function __construct() {
    }

    /**
     * @return \origin\mvc\Mvc
     */
    public static function getInstance() {
        if (empty(self::$_self)) {
            self::$_self = new Mvc();
        }
        return self::$_self;
    }

    /**
     * codepoint path format like: '/www/project', if $application_name = 'application', will set:
     * actoin root path = /internal/application/actions,   namespace = application\actions  
     * view root path = /frontend/templates/application,   namespace = application
     * if $codepoint_path === FALSE, will not config action and view path, in case for CLI don't need them
     * @param string $codepoint_path
     * @param string $application_name
     * @return \origin\mvc\Mvc
     */
    public function initMvc($codepoint_path = FALSE, $application_name = 'application') {
        if ($this->_controller) {
            return $this;
        }
        include 'origin/loader/Autoloader.php';
        Autoloader::getInstance()->registerAutoloader();
        $this->_controller = new Controller();
        $this->_view = $this->_controller->getView();
        if ($codepoint_path !== FALSE) {
            $this->setActionPathConfig($this->_controller->getRouter(), $codepoint_path, $application_name);
            $this->setViewPathConfig($this->_view, $codepoint_path, $application_name);
        }
        return $this;
    }

    /**
     * Route to action script and run it. Before using this, ensure the functions called:
     * initMvc() 
     * @param string $allow_types
     * @param callable $callback_404
     * @param callable $action_finder
     */
    public function startAction($allow_types = FALSE, $callback_404 = FALSE, $action_finder = FALSE) {
        $this->getController()->runAction($allow_types, $callback_404, $action_finder);
    }

    /**
     * Get FormPlus object
     * @return \origin\form\FormPlus
     */
    public final function getForm() {
        if (! $this->_form) {
            $this->_form = FormPlus::getInstance();
        }
        return $this->_form;
    }

    /**
     * @return \origin\mvc\View
     */
    public function getView() {
        return $this->_view;
    }

    /**
     * Create an independent View. Scenario: in case without origin controller or router, or want a pure View instance
     * codepoint path format like: '/www/project', if $application_name = 'application', will set:
     * view root path = /frontend/templates/application,   namespace = application
     * @param string $codepoint_path
     * @param string $application_name
     * @return \origin\mvc\View
     */
    public function createIndependentView($codepoint_path, $application_name = 'application') {
        $view = new View();
        $this->setViewPathConfig($view, $codepoint_path, $application_name);
        if (! $this->_view) {
            $this->_view = $view;
        }
        return $view;
    }

    /**
     * @throws \UnexpectedValueException
     * @return \origin\mvc\Action
     */
    public function getAction() {
        $action = $this->_action ? $this->_action : $this->getController()->getAction();
        if ($action) {
            return $action;
        }
        throw new \LogicException('Action instance not exists');
    }

    /**
     * Set an own action instance
     * @param Action $action
     */
    public function setAction(Action $action) {
        $action->setController($this->getController());
        $this->_action = $action;
    }

    /**
     * @return \origin\mvc\Controller
     */
    private function getController() {
        if ($this->_controller) {
            return $this->_controller;
        }
        throw new \LogicException('Controller not set yet');
    }

    private function setActionPathConfig(Router $router, $codepoint_path, $application_name) {
        $router->setActionRootPath($codepoint_path . '/internal/' . $application_name . '/actions', $application_name . '\\actions');
    }

    private function setViewPathConfig(View $view, $codepoint_path, $application_name) {
        $view->setViewRootPath($codepoint_path . '/frontend/templates/' . $application_name, $application_name);
    }
}

?>