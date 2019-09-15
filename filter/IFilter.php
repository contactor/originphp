<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\filter;

/**
 * Filter interface.
 * The definition copied from Zend Framework 2, so it's compatible except namespace.
 * @author Johnson Tsang <contactor@gmail.com> 2013-01-23
 */
interface IFilter {

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value);
}

?>