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
 * Selecte Options Html Component.
 * @author Johnson Tsang <contactor@gmail.com> 2011-11-27
 */
class SelectedOptions implements HtmlComponent {
    private $_select_options;
    private $_selected_item;

    /**
     * Set selected options and selected item
     * @param array $select_options
     * @param string $selected_item
     * @return \origin\hc\SelectedOptions
     */
    public function setData($select_options, $selected_item) {
        $this->_select_options = $select_options;
        $this->_selected_item = $selected_item;
        return $this;
    }

    /**
     * Format HTML component to HTML string 
     * @return string
     */
    public function toHtml() {
        if (empty($this->_select_options)) {
            return '';
        }
        
        $html = '';
        $selected = $this->_selected_item;
        foreach ($this->_select_options as $key => $value) {
            $html .= '<option value="' . $key;
            if ($selected == $key) {
                $html .= '" selected>';
            } else {
                $html .= '">';
            }
            $html .= $value . '</option>';
        }
        return $html;
    }
}

?>