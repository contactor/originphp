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
 * Form helper.
 * @author Johnson Tsang <johnson@eppbar.org> 2011-03-12
 */
class FormPlus {
    private static $_self;
    private $_form_parser;

    private function __construct() {
    }

    /**
     * @return \origin\form\FormPlus
     */
    public static function getInstance() {
        if (empty(self::$_self)) {
            self::$_self = new FormPlus();
        }
        return self::$_self;
    }

    /**
     * Get FormParser instance
     * @return \origin\form\FormParser
     */
    public function getFormParser() {
        if (! $this->_form_parser) {
            $this->_form_parser = new FormParser();
        }
        return $this->_form_parser;
    }

    /**
     * Parse form varibles.
     * Pass an array of FormField and a data entity object into this method,
     * if success, the entity contains the form data,
     * if fail, the entity contains the form data and error message.
     * If fail and if form hint entity is not empty, the error message will extract into form hint and,
     * If parameter $against_xss is TRUE, form data will be filted by htmlspecialchars() function.
     * @param Array of FormField $form_fields or FormField if only one form field
     * @param Object $form_data
     * @param Object $form_hint
     * @param boolean $against_xss
     * @return FALSE if success, or TRUE if error occurred.
     */
    public function parseForm($form_fields, $form_data, $form_hint = FALSE, $against_xss = TRUE) {
        $fp = $this->getFormParser();
        try {
            $fp->parse($form_fields, $form_data);
            return FALSE;
        } catch (\Exception $e) {
            if ($form_hint) {
                $fp->setFormErrorHint($form_hint);
                if ($against_xss) {
                    $fp->makeFormDataHtmlSafe($form_fields, $form_data);
                }
            }
            return TRUE;
        }
    }

    /**
     * Check if is post data exists.
     */
    public function hasPostData() {
        return ! empty($_POST);
    }

    /**
     * Check if is post method.
     */
    public function isPostMethod() {
        return (strtolower($_SERVER['REQUEST_METHOD']) === 'post') ? TRUE : FALSE;
    }

    /**
     * Make veriable safe to display as HTML text
     * Only process one layer string data(the data type could be: number/boolean/string/object/array) except
     * $is_array is true, could process 2 layer data(top layer must be array)
     * @param mixed $data
     * @param boolean $is_array
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function toSafeHtml($data, $is_array = FALSE) {
        $toSafeHtml_func = function ($data) {
            if (empty($data) || is_numeric($data) || is_bool($data)) {
                return $data;
            }
            if (is_string($data)) {
                return htmlspecialchars($data, ENT_QUOTES);
            }
            if (is_object($data)) {
                $array = get_object_vars($data);
                foreach ($array as $key => $value) {
                    if (is_string($value)) {
                        $data->$key = htmlspecialchars($value, ENT_QUOTES);
                    }
                }
                return $data;
            }
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if (is_string($value)) {
                        $value = htmlspecialchars($value, ENT_QUOTES);
                    }
                    $data[$key] = $value;
                }
                return $data;
            }
            return $data;
        };
        
        if ($is_array) {
            if (! is_array($data)) {
                throw new \InvalidArgumentException('Invalid toSafeHtml parameters');
            }
            foreach ($data as $key => $value) {
                $data[$key] = $toSafeHtml_func($value);
            }
            return $data;
        }
        return $toSafeHtml_func($data);
    }
}

?>