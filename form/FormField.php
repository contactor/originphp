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
 * Form field properties definition.
 * @author Johnson Tsang <johnson@eppbar.org> 2011-02-14
 */
class FormField {
    private $_field_name;
    private $_key_name;
    private $_http_method = HttpMethod::POST;
    private $_http_method_param;
    private $_http_method_filter;
    private $_data_type = DataType::STRING;
    private $_is_file_type = FALSE;
    private $_is_required = FALSE;
    private $_required_message;
    private $_default_value = NULL;
    private $_is_array = FALSE;
    private $_is_required4_array = FALSE;
    private $_required_message4_array;
    private $_default_value4_array = array();
    private $_validator_filters = array();
    private $_validator_filters4_array = array();
    private $_default_value_filters = array();
    private $_default_value_filters4_array = array();
    private $_is_invalid = FALSE;
    private $_value;
    private $_error_message = 'Invalid data type';

    /**
     * Constructor.
     * @param string $field_name
     * @param string $key_name
     */
    public function __construct($field_name, $key_name = TRUE) {
        $this->_field_name = $field_name;
        $this->_key_name = $key_name;
    }

    /**
     * Set class field name
     * @param string $field_name
     * @return \origin\form\FormField
     */
    public function setFieldName($field_name) {
        $this->_field_name = $field_name;
        return $this;
    }

    /**
     * Set from hint key name
     * @param unknown $key_name
     * @return \origin\form\FormField
     */
    public function setKeyName($key_name) {
        $this->_key_name = $key_name;
        return $this;
    }

    /**
     * Set HTTP method
     * @param string $http_method
     * @param variant $http_method_param
     * @return \origin\form\FormField
     */
    public function setHttpMethod($http_method, $http_method_param = FALSE) {
        $this->_http_method = $http_method;
        $this->_http_method_param = $http_method_param;
        return $this;
    }

    /**
     * Set HTTP method filter
     * @param callable $http_method_filter
     * @return \origin\form\FormField
     */
    public function setHttpMethodFilter($http_method_filter) {
        $this->_http_method_filter = $http_method_filter;
        return $this;
    }

    /**
     * Set data type
     * @param integer $data_type
     * @return \origin\form\FormField
     */
    public function setDataType($data_type) {
        if ($data_type < DataType::_ARRAY_BASE) {
            $this->_data_type = $data_type;
            $this->_is_array = FALSE;
        } else {
            $this->_data_type = $data_type - DataType::_ARRAY_BASE;
            $this->_is_array = TRUE;
        }
        if ($this->_data_type == DataType::FILE) {
            $this->_is_file_type = TRUE;
        } else {
            $this->_is_file_type = FALSE;
        }
        return $this;
    }

    /**
     * Set field value must exist or treat as an error.
     * @param string $error_message
     * @return \origin\form\FormField
     */
    public function setRequired($error_message) {
        $this->_is_required = TRUE;
        $this->_required_message = $error_message;
        return $this;
    }

    /**
     * Set default value. DON'T use FALSE because FormParser use FALSE represent data is invalid
     * @param variant $default_value
     * @return \origin\form\FormField
     */
    public function setDefaultValue($default_value) {
        if ($default_value === FALSE) {
            throw new \InvalidArgumentException('Field default value can not be FALSE');
        }
        $this->_is_required = FALSE;
        $this->_default_value = $default_value;
        return $this;
    }

    /**
     * Set field value must exist or treat as an error.
     * @param string $error_message
     * @return \origin\form\FormField
     */
    public function setRequired4ArrayType($error_message) {
        $this->_is_required4_array = TRUE;
        $this->_required_message4_array = $error_message;
        return $this;
    }

    /**
     * Set default value for array type data. DON'T use FALSE because FormParser use FALSE represent data is invalid
     * @param array $default_value
     * @return \origin\form\FormField
     */
    public function setDefaultValue4ArrayType($default_value) {
        if ($default_value === FALSE) {
            throw new \InvalidArgumentException('Field default value can not be FALSE');
        }
        $this->_is_required4_array = FALSE;
        $this->_default_value4_array = $default_value;
        return $this;
    }

    /**
     * Add a validator.
     * If $validator is a callable function, return FALSE or error message
     * @param \origin\validator\IValidator or callable $validator
     * @return \origin\form\FormField
     */
    public function addValidator($validator) {
        $this->_validator_filters[] = is_callable($validator) ? array($validator, TRUE) : $validator;
        return $this;
    }

    /**
     * Add a filter
     * @param \origin\filter\IFilter or callable $filter
     * @return \origin\form\FormField
     */
    public function addFilter($filter) {
        $this->_validator_filters[] = is_callable($filter) ? array($filter, FALSE) : $filter;
        return $this;
    }

    /**
     * Add a filter for default value
     * @param \origin\filter\IFilter or callable $filter
     * @return \origin\form\FormField
     */
    public function addDefaultValueFilter($filter) {
        $this->_default_value_filters[] = is_callable($filter) ? array($filter, FALSE) : $filter;
        return $this;
    }

    /**
     * Add a validator.
     * If $validator is a callable function, return FALSE or error message
     * @param \origin\validator\IValidator or callable $validator
     * @return \origin\form\FormField
     */
    public function addValidator4ArrayType($validator) {
        $this->_validator_filters4_array[] = is_callable($validator) ? array($validator, TRUE) : $validator;
        return $this;
    }

    /**
     * Add a filter for array type
     * @param \origin\filter\IFilter or callable $filter
     * @return \origin\form\FormField
     */
    public function addFilter4ArrayType($filter) {
        $this->_validator_filters4_array[] = is_callable($filter) ? array($filter, FALSE) : $filter;
        return $this;
    }

    /**
     * Add a filter for default value for array type
     * @param \origin\filter\IFilter or callable $filter
     * @return \origin\form\FormField
     */
    public function addDefaultValueFilter4ArrayType($filter) {
        $this->_default_value_filters4_array[] = is_callable($filter) ? array($filter, FALSE) : $filter;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName() {
        return $this->_field_name;
    }

    /**
     * @return string
     */
    public function getKeyName() {
        if ($this->_key_name === FALSE) {
            return FALSE;
        }
        if ($this->_key_name === TRUE) {
            return $this->getFieldName();
        }
        return $this->_key_name;
    }

    /**
     * @return integer
     */
    public function getHttpMethod() {
        return $this->_http_method;
    }

    /**
     * @return variant
     */
    public function getHttpMethodParam() {
        return $this->_http_method_param;
    }

    /**
     * @return variant
     */
    public function getHttpMethodFilter() {
        return $this->_http_method_filter;
    }

    /**
     * @return integer
     */
    public function getDataType() {
        return $this->_data_type;
    }

    /**
     * @return integer
     */
    public function isFileType() {
        return $this->_is_file_type;
    }

    /**
     * @return boolean
     */
    public function isRequired() {
        return $this->_is_required;
    }

    /**
     * @return string
     */
    public function getRequiredMessage() {
        return $this->_required_message;
    }

    /**
     * @return variant
     */
    public function getDefaultValue() {
        return $this->_default_value;
    }

    /**
     * @return boolean
     */
    public function isArray() {
        return $this->_is_array;
    }

    /**
     * @return boolean
     */
    public function isRequired4ArrayType() {
        return $this->_is_required4_array;
    }

    /**
     * @return string
     */
    public function getRequiredMessage4ArrayType() {
        return $this->_required_message4_array;
    }

    /**
     * @return variant
     */
    public function getDefaultValue4ArrayType() {
        return $this->_default_value4_array;
    }

    /**
     * @return array
     */
    public function getValidatorFilters() {
        return $this->_validator_filters;
    }

    /**
     * @return array
     */
    public function getDefaultValueFilters() {
        return $this->_default_value_filters;
    }

    /**
     * @return array
     */
    public function getValidatorFilters4ArrayType() {
        return $this->_validator_filters4_array;
    }

    /**
     * @return array
     */
    public function getDefaultValueFilters4ArrayType() {
        return $this->_default_value_filters4_array;
    }

    /**
     * @return boolean
     */
    public function isInvalid() {
        return $this->_is_invalid;
    }

    /**
     * @param string $key
     * @return variant
     */
    public function getValue($key = FALSE) {
        if ($key === FALSE) {
            return $this->_value;
        } else {
            return $this->_value[$key];
        }
    }

    /**
     * @return string
     */
    public function getErrorMessage() {
        return $this->_error_message;
    }

    /**
     * @param variant $value
     * @param string $key
     */
    public function setValue($value, $key = FALSE) {
        if ($key === FALSE) {
            $this->_value = $value;
        } else {
            $this->_value[$key] = $value;
        }
    }

    /**
     * @param variant $value
     */
    public function setValid($value) {
        $this->_is_invalid = FALSE;
        $this->_value = $value;
    }

    /**
     * @param string $error_message
     */
    public function setInvalid($error_message) {
        $this->_is_invalid = TRUE;
        $this->_error_message = $error_message;
    }

    /**
     * Create a new FormField instance 
     * @param string $field_name
     * @param string $key_name
     * @return \origin\form\FormField
     */
    public static function newInstance($field_name, $key_name = TRUE) {
        return new FormField($field_name, $key_name);
    }
}

?>