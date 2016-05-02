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
use origin\mvc\Mvc;

/**
 * Translate message in view file.
 * @param string $message
 */
function lang($message) {
    echo Mvc::getInstance()->getView()->translateViewMessage($message);
}

/**
 * Translate message in code file.
 * @param string $message
 */
function loc($message) {
    return Mvc::getInstance()->getView()->translateCodeMessage($message);
}

