<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\mvc;

/**
 * MVC Routing information. Internal use only.
 * @author Johnson Tsang <contactor@gmail.com> 2012-03-16
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