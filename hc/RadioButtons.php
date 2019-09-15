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
 * Radio Buttons Html Component.
 * @author Johnson Tsang <contactor@gmail.com> 2013-05-17
 */
class RadioButtons implements HtmlComponent {
    private $_radio_name;
    private $_radio_options;
    private $_checked_item;

    /**
     * Set radio options and checked item
     * @param string $radio_name
     * @param array $radio_options
     * @param string $checked_item
     * @return \origin\hc\RadioButtons
     */
    public function setData($radio_name, $radio_options, $checked_item) {
        $this->_radio_name = $radio_name;
        $this->_radio_options = $radio_options;
        $this->_checked_item = $checked_item;
        return $this;
    }

    /**
     * Format HTML component to HTML string 
     * @return string
     */
    public function toHtml() {
        if (empty($this->_radio_options) || empty($this->_radio_name)) {
            return '';
        }
        
        $html = '';
        $item = '<input type="radio" name="' . $this->_radio_name . '" value="';
        $checked = $this->_checked_item;
        foreach ($this->_radio_options as $key => $value) {
            $html .= $item . $key;
            if ($checked == $key) {
                $html .= '" checked="checked">';
            } else {
                $html .= '">';
            }
            $html .= $value;
        }
        return $html;
    }
}

?>