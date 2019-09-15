<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\form;

/**
 * Data type consts.
 * @author Johnson Tsang <contactor@gmail.com> 2011-02-14
 */
class DataType {
    /**
     * single type
     */
    const STRING = 1;
    const INT = 2;
    const FLOAT = 3;
    const FILE = 4;
    const OBJECT = 5;
    const ARRAY_TYPE = 6;
    
    /**
     * Internal use: Array type base index
     */
    const _ARRAY_BASE = 10;
    
    /**
     * Array type
     */
    const STRING_ARRAY = 11;
    const INT_ARRAY = 12;
    const FLOAT_ARRAY = 13;
    const FILE_ARRAY = 14;
    const OBJECT_ARRAY = 15;
    const ARRAY_ARRAY = 16;
}

?>