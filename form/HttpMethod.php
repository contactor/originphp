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
 * HTTP method define.
 * The const value equals the HttpMethodHelper class method name. 
 * @author Johnson Tsang <contactor@gmail.com> 2011-02-14
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