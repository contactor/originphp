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
 * MVC Routing information. Internal use only.
 * @author Johnson Tsang <johnson@eppbar.org> 2012-03-16
 */
class RoutingInfo {
    public $action_url;
    public $action_script_dir;
    public $action_instance;
    public $action_class_name;
    public $action_args;
    public $bootstrap_script_path;
    public $bootstrap_class_name;
}

?>