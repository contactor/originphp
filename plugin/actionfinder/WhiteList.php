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
namespace origin\plugin\actionfinder;

/**
 * Implementation action finder by white list.
 * @author Johnson Tsang <johnson@eppbar.org> 2014-08-29
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