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
namespace origin\validator;

/**
 * A simple abstract validator, implemented getMessages().
 * @author Johnson Tsang <johnson@eppbar.org> 2013-01-23
 */
abstract class OriginValidator implements IValidator {
    private $_message;

    public function __construct($message = '') {
        $this->_message = $message;
    }

    public function getMessages() {
        return $this->_message;
    }
}

?>