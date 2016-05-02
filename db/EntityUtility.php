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
 * Database table entity utility.
 * @author Johnson Tsang <johnson@eppbar.org> 2015-07-08
 */
class EntityUtility {

    /**
     * Merge a object into another object(entity)
     * @param object/object array $into_object
     * @param object/object array $ext_object
     */
    public static function mergeObject($into_object, $ext_object) {
        $merge_func = function ($into_object, $ext_object) {
            if (! is_object($into_object) || ! is_object($ext_object)) {
                throw new \InvalidArgumentException('Invalid object to merge');
            }
            $vars = get_object_vars($ext_object);
            if ($vars) {
                foreach ($vars as $key => $value) {
                    if (! property_exists($into_object, $key)) {
                        $into_object->$key = $value;
                    }
                }
            }
        };
        if (is_array($into_object)) {
            if (! is_array($ext_object) || count($into_object) != count($ext_object)) {
                throw new \InvalidArgumentException('into/ext object not match');
            }
            foreach ($into_object as $key => $into) {
                $merge_func($into, $ext_object[$key]);
            }
            return;
        }
        $merge_func($into_object, $ext_object);
    }
}

?>