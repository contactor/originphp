<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\hc;

/**
 * Html Component Interface.
 * @author Johnson Tsang <contactor@gmail.com> 2011-11-27
 */
interface HtmlComponent {

    /**
     * Format HTML component to HTML string 
     * @return string
     */
    public function toHtml();
}

?>