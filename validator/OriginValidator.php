<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\validator;

/**
 * A simple abstract validator, implemented getMessages().
 * @author Johnson Tsang <contactor@gmail.com> 2013-01-23
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