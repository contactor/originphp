<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\plugin\actionfinder;

/**
 * Implementation action finder by white list.
 * @author Johnson Tsang <contactor@gmail.com> 2014-08-29
 */
class WhiteList {
    private $_white_list = FALSE;

    /**
     * @param array $list
     */
    public function setWhiteList($list) {
        if (! is_array($list)) {
            throw new \InvalidArgumentException('Invalid white list');
        }
        $this->_white_list = $list;
    }

    /**
     * @param string $action_root
     * @param string $url
     */
    public function findAction($action_root, $url) {
        if (empty($this->_white_list)) {
            return FALSE;
        }
        
        $list = $this->_white_list;
        $args = array();
        while (TRUE) {
            if (in_array($url, $list)) {
                return array('action' => $url, 'args' => $args);
            }
            $pos = strrpos($url, '/');
            if ($pos === FALSE) {
                return FALSE;
            }
            $pos2 = $pos + 1;
            if (strlen($url) != $pos2) {
                $args[] = substr($url, $pos2);
            }
            $url = substr($url, 0, $pos);
        }
    }
}

?>