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
namespace origin\db;

/**
 * PDO statment cache.
 * @author Johnson Tsang <johnson@eppbar.org> 2014-09-28
 */
class StatmentCache {
    private $_statments = [];

    /**
     * Append statment to cache pool
     * @param object $statment
     * @param string $sql
     */
    public function appendStatment($statment, $sql) {
        $this->checkSql($sql);
        if (! isset($this->_statments[$sql])) {
            if (! is_object($statment)) {
                throw new \InvalidArgumentException('Cache fail: Invalid STMT');
            }
            $this->_statments[$sql] = $statment;
        }
    }

    /**
     * Retrieve statment from cache pool
     * @param string $sql
     */
    public function retrieveStatment($sql) {
        $this->checkSql($sql);
        return isset($this->_statments[$sql]) ? $this->_statments[$sql] : FALSE;
    }

    /**
     * Clear statment cache pool
     */
    public function clearCache() {
        $this->_statments = [];
    }

    private function checkSql($sql) {
        if (! is_string($sql) || empty($sql)) {
            throw new \InvalidArgumentException('Cache fail: Invalid SQL string');
        }
    }
}

?>