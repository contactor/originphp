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
namespace origin\form;

/**
 * HTTP method define.
 * The const value equals the HttpMethodHelper class method name. 
 * @author Johnson Tsang <johnson@eppbar.org> 2011-02-14
 */
class HttpMethod {
    /**
     * HTTP method GET.
     * @var string
     */
    const GET = 'get';
    
    /**
     * HTTP method POST.
     * @var string
     */
    const POST = 'post';
    
    /**
     * HTTP method GET or POST.
     * @var string
     */
    const GET_OR_POST = 'getOrPost';
    
    /**
     * From array key value.
     * @var string
     */
    const ARRAY_KEY_VALUE = 'arrayKeyValue';
    
    /**
     * Already got the data.
     * @var string
     */
    const IN_HAND = 'inHand';
}

?>