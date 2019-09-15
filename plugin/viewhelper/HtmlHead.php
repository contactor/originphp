<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\plugin\viewhelper;

/**
 * Generate HTML head helper.
 * @author Johnson Tsang <contactor@gmail.com> 2014-11-24
 */
class HtmlHead {
    private $_title = '';
    private $_meta = array();
    private $_link = array();
    private $_css = array();
    private $_js = array();

    /**
     * Get title
     * @return string
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
     * Set title
     * @param string $title
     */
    public function setTitle($title) {
        $this->_title = $title;
    }

    /**
     * echo html title
     */
    public function title() {
        echo $this->_title;
    }

    /**
     * Get metas
     * @return array
     */
    public function getMeta() {
        return $this->_meta;
    }

    /**
     * Set metas
     * @param array $meta
     */
    public function setMeta($meta) {
        $this->_meta = is_array($meta) ? $meta : array($meta);
    }

    /**
     * Add a meta
     * @param string/string array $meta
     */
    public function addMeta($meta) {
        if (is_array($meta)) {
            $this->_meta = array_merge($this->_meta, $meta);
        } else {
            $this->_meta[] = $meta;
        }
    }

    /**
     * echo metas as html string
     */
    public function meta() {
        if (! $this->_meta) {
            return;
        }
        foreach ($this->_meta as $value) {
            echo $value;
            echo "\n";
        }
    }

    /**
     * Get links
     * @return array
     */
    public function getLink() {
        return $this->_link;
    }

    /**
     * Set links
     * @param array $link
     */
    public function setLink($link) {
        $this->_link = is_array($link) ? $link : array($link);
    }

    /**
     * Add a link
     * @param string/string array $rel
     * @param string/string array $href
     */
    public function addLink($rel, $href) {
        if (is_array($rel)) {
            if (count($rel) != count($href)) {
                throw new \InvalidArgumentException('ref and href not match');
            }
            foreach ($rel as $key => $value) {
                $this->_link[$value] = $href[$key];
            }
        } else {
            $this->_link[$rel] = $href;
        }
    }

    /**
     * echo links as html string
     */
    public function link() {
        if (! $this->_link) {
            return;
        }
        foreach ($this->_link as $rel => $href) {
            echo '<link rel="' . $rel . '" href="' . $href . '" />';
            echo "\n";
        }
    }

    /**
     * Get CSS links
     * @return array
     */
    public function getCss() {
        return $this->_css;
    }

    /**
     * Set CSS links
     * @param array $css
     */
    public function setCss($css) {
        $this->_css = is_array($css) ? $css : array($css);
    }

    /**
     * Add a CSS link
     * @param string/string array $css
     */
    public function addCss($css) {
        if (is_array($css)) {
            $this->_css = array_merge($this->_css, $css);
        } else {
            $this->_css[] = $css;
        }
    }

    /**
     * echo CSS links as html string
     */
    public function css() {
        if (! $this->_css) {
            return;
        }
        $css_array = array_unique($this->_css);
        foreach ($css_array as $value) {
            echo '<link href="' . $value . '" rel="stylesheet" type="text/css" />';
            echo "\n";
        }
    }

    /**
     * Get JS links
     * @return array
     */
    public function getJs() {
        return $this->_js;
    }

    /**
     * Set JS links
     * @param array $js
     */
    public function setJs($js) {
        $this->_js = is_array($js) ? $js : array($js);
    }

    /**
     * Add a JS link
     * @param string/string array $js
     */
    public function addJs($js) {
        if (is_array($js)) {
            $this->_js = array_merge($this->_js, $js);
        } else {
            $this->_js[] = $js;
        }
    }

    /**
     * echo JS links as html string
     */
    public function js() {
        if (! $this->_js) {
            return;
        }
        $js_array = array_unique($this->_js);
        foreach ($js_array as $value) {
            echo '<script type="text/javascript" src="' . $value . '"></script>';
            echo "\n";
        }
    }
}

?>