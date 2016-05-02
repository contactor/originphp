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
 * HTTP method getter helper.
 * This class function name should refer HttpMethod class define.
 * Following the function rules, you can extend the HTTP method getter helper.
 * The getter function name should be the same as defined in the const. This is a rule.
 * For excample, if define HttpMethod const POST = 'post'; the getter function name should be post, as:
 * public function post(FormField $field). 
 * @author Johnson Tsang <johnson@eppbar.org> 2011-02-14
 */
class HttpMethodHelper {

    public function get(FormField $field) {
        if (isset($_GET[$field->getFieldName()])) {
            return $_GET[$field->getFieldName()];
        } else {
            return FALSE;
        }
    }

    public function post(FormField $field) {
        if ($field->isFileType()) {
            return $this->uploadFile($field);
        }
        if (isset($_POST[$field->getFieldName()])) {
            return $value = $_POST[$field->getFieldName()];
        } else {
            return FALSE;
        }
    }

    public function getOrPost(FormField $field) {
        if (isset($_GET[$field->getFieldName()])) {
            return $_GET[$field->getFieldName()];
        }
        return $this->post($field);
    }

    public function arrayKeyValue(FormField $field) {
        $array = $field->getHttpMethodParam();
        $key = $field->getFieldName();
        return (is_array($array) && isset($array[$key])) ? $array[$key] : FALSE;
    }

    public function inHand(FormField $field) {
        return $field->getHttpMethodParam();
    }

    private function uploadFile(FormField $field) {
        $fname = $field->getFieldName();
        if (isset($_FILES[$fname])) {
            $files = $_FILES[$fname];
            if (is_array($files['error'])) {
                $value = array();
                foreach ($files['size'] as $key => $size) {
                    if ($size == 0 || ! is_uploaded_file($files['tmp_name'][$key])) {
                        $value[] = FALSE;
                    } else {
                        $file = new UploadedFile();
                        $file->name = $files['name'][$key];
                        $file->type = $files['type'][$key];
                        $file->size = $size;
                        $file->tmp_name = $files['tmp_name'][$key];
                        $file->error = $files['error'][$key];
                        $value[] = $file;
                    }
                }
            } else {
                if ($files['size'] == 0 || ! is_uploaded_file($files['tmp_name'])) {
                    return FALSE;
                }
                $file = new UploadedFile();
                $file->name = $files['name'];
                $file->type = $files['type'];
                $file->size = $files['size'];
                $file->tmp_name = $files['tmp_name'];
                $file->error = $files['error'];
                $value = $file;
            }
            return $value;
        } else {
            return FALSE;
        }
    }
}

?>