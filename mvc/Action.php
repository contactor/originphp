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

use origin\form\FormPlus;

/**
 * MVC Action.
 * @author Johnson Tsang <johnson@eppbar.org> 2013-01-07
 */
class Action {
    
    /**
     * Controller object
     * @var origin\mvc\Controller
     */
    private $_controller;
    
    /**
     * View object
     * @return \origin\mvc\View
     */
    private $_view;

    /**
     * Get FormPlus object
     * @return \origin\form\FormPlus
     */
    public final function getForm() {
        return FormPlus::getInstance();
    }

    /**
     * @return \origin\mvc\View
     */
    public final function getView() {
        if (empty($this->_view)) {
            throw new \LogicException('View not set yet');
        }
        return $this->_view;
    }

    /**
     * Set Controller object
     * @param origin\mvc\Controller $controller
     */
    public final function setController(Controller $controller) {
        $this->_controller = $controller;
        $this->_view = $controller->getView();
    }

    /**
     * Display layout view.
     * @param string $layout_filename
     * @param string or empty $content_filename
     * @param boolean $layout_translation
     * @param boolean $content_translation
     */
    public function renderView($layout_filename, $content_filename = FALSE, $layout_translation = FALSE, $content_translation = FALSE, $is_to_string = FALSE) {
        $this->getRouter()->setIsRenderView(FALSE);
        $view = $this->getView();
        if (method_exists($this, 'onPrepareView')) {
            $this->onPrepareView($view);
        }
        return $view->render($layout_filename, $content_filename, $layout_translation, $content_translation, $is_to_string);
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
    protected final function runActionMethod($allow_methods, $has_default_method = TRUE) {
        return $this->getRouter()->routeToActionMethod($allow_methods, $has_default_method);
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

    /**
     * @return \origin\mvc\Router
     */
    private function getRouter() {
        return $this->getController()->getRouter();
    }
}

?>