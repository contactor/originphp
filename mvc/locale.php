<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
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

