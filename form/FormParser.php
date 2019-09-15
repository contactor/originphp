<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\form;

use origin\validator\IValidator;

/**
 * Parse all form fields.
 * @author Johnson Tsang <contactor@gmail.com> 2011-02-14
 */
class FormParser {
    private $_is_check_all_fields = TRUE;
    private $_http_method_helper = FALSE;
    private $_invalid_fields = array();

    /**
     * @param boolean $is_check_all_fields
     * @return \origin\form\FormParser
     */
    public function setCheckAllFields($is_check_all_fields = TRUE) {
        $this->_is_check_all_fields = $is_check_all_fields;
        return $this;
    }

    /**
     * @param object $http_method_helper
     * @return \origin\form\FormParser
     */
    public function setHttpMethodHelper($http_method_helper) {
        $this->_http_method_helper = $http_method_helper;
        return $this;
    }

    /**
     * @return array
     */
    public function getInvalidFields() {
        return $this->_invalid_fields;
    }

    /**
     * Parse form values.
     * @param array of FormField $form_fields or FormField if only one form field.
     * @param object $class.
     * @throws FormException
     */
    public function parse($form_fields, $class) {
        if (! is_object($class)) {
            throw new \UnexpectedValueException("Invalid parameter class");
        }
        if (empty($form_fields)) {
            throw new \UnexpectedValueException("Invalid parameter form_fields");
        }
        if (! is_array($form_fields)) {
            $form_fields = array($form_fields);
        }
        foreach ($form_fields as $field) {
            if (! $field instanceof FormField) {
                throw new \UnexpectedValueException("Invalid parameter form_fields");
            }
        }
        
        if (! is_object($this->_http_method_helper)) {
            $this->_http_method_helper = new HttpMethodHelper();
        }
        $this->_invalid_fields = array();
        
        foreach ($form_fields as $field) {
            $this->parseField($field);
            if ($field->isInvalid() && ! $this->_is_check_all_fields) {
                break;
            }
        }
        
        $class = $this->dumpFormValue($form_fields, $class);
        if (! empty($this->_invalid_fields)) {
            throw new FormException($this->_invalid_fields[0]->getErrorMessage());
        }
    }

    private function parseField(FormField $field) {
        $value = $this->getFieldValue($field);
        if ($value === FALSE) {
            return;
        }
        
        $value = $this->translateDataType($field);
        if ($value === FALSE) {
            return;
        }
        $field->setValue($value);
        
        $has_default_value = ! $field->isRequired();
        $default_value = $field->getDefaultValue();
        $validators = $field->getValidatorFilters();
        if ($field->isArray()) {
            // validate array items
            foreach ($value as $key => $item) {
                if ($has_default_value && $item == $default_value) {
                    if ($this->filterDefaultValue($field, $key)) {
                        return;
                    }
                } else {
                    foreach ($validators as $validator) {
                        if ($this->validateFieldValue($field, $validator, $key)) {
                            return;
                        }
                    }
                }
            }
            // validate whole array
            if (! $field->isRequired4ArrayType() && $field->getValue() == $field->getDefaultValue4ArrayType()) {
                if ($this->filterDefaultValue4ArrayType($field)) {
                    return;
                }
            } else {
                foreach ($field->getValidatorFilters4ArrayType() as $validator) {
                    if ($this->validateFieldValue($field, $validator)) {
                        return;
                    }
                }
            }
        } else {
            if ($has_default_value && $value == $default_value) {
                if ($this->filterDefaultValue($field)) {
                    return;
                }
            } else {
                foreach ($validators as $validator) {
                    if ($this->validateFieldValue($field, $validator)) {
                        return;
                    }
                }
            }
        }
    }

    private function filterDefaultValue(FormField $field, $key = FALSE) {
        $filters = $field->getDefaultValueFilters();
        if (empty($filters)) {
            return FALSE;
        }
        foreach ($filters as $filter) {
            if ($this->validateFieldValue($field, $filter, $key)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function filterDefaultValue4ArrayType(FormField $field) {
        $filters = $field->getDefaultValueFilters4ArrayType();
        if (empty($filters)) {
            return FALSE;
        }
        foreach ($filters as $filter) {
            if ($this->validateFieldValue($field, $filter)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function getFieldValue(FormField $field) {
        $class = $this->_http_method_helper;
        $method = $field->getHttpMethod();
        $value = $class->$method($field);
        $method_filter = $field->getHttpMethodFilter();
        if (is_callable($method_filter)) {
            try {
                $value = call_user_func($method_filter, $value);
            } catch (\Exception $e) {
                $this->set_Field_Value_Is_Invalid($field, $e->getMessage());
                return FALSE;
            }
        }
        $field->setValue($value);
        if (! ($value === FALSE)) {
            if ($field->isArray()) {
                if (empty($value)) {
                    $value = FALSE;
                } else {
                    if (! is_array($value)) {
                        $value = array($value);
                    }
                    $oldValue = $value;
                    $value = array();
                    foreach ($oldValue as $item) {
                        $item = $this->trimFieldValue($field, $item);
                        if ($item === FALSE) {
                            if ($field->isRequired()) {
                                $this->set_Field_Value_Is_Invalid($field, $field->getRequiredMessage());
                                return FALSE;
                            }
                            $item = $field->getDefaultValue();
                        }
                        $value[] = $item;
                    }
                }
            } else {
                if (is_array($value)) {
                    $this->set_Field_Value_Is_Invalid($field, $field->getErrorMessage());
                    return FALSE;
                }
                $value = $this->trimFieldValue($field, $value);
            }
        }
        
        if ($value === FALSE) {
            if ($field->isArray()) {
                if ($field->isRequired4ArrayType()) {
                    $this->set_Field_Value_Is_Invalid($field, $field->getRequiredMessage4ArrayType());
                } else {
                    $field->setValid($field->getDefaultValue4ArrayType());
                    if ($this->filterDefaultValue4ArrayType($field)) {
                        return FALSE;
                    }
                }
            } else {
                if ($field->isRequired()) {
                    $this->set_Field_Value_Is_Invalid($field, $field->getRequiredMessage());
                } else {
                    $field->setValid($field->getDefaultValue());
                    if ($this->filterDefaultValue($field)) {
                        return FALSE;
                    }
                }
            }
        }
        return $value;
    }

    private function trimFieldValue(FormField $field, $value) {
        $data_type = $field->getDataType();
        if ($data_type == DataType::STRING || $data_type == DataType::INT || $data_type == DataType::FLOAT) {
            $value = trim($value);
            if ($value == '') {
                return FALSE;
            }
        }
        return $value;
    }

    private function set_Field_Value_Is_Invalid(FormField $field, $error_message) {
        $field->setInvalid($error_message);
        $this->_invalid_fields[] = $field;
    }

    private function translateDataType(FormField $field) {
        $value = $field->getValue();
        if ($field->getDataType() == DataType::STRING || $field->getDataType() == DataType::FILE) {
            return $value;
        }
        if ($field->isArray()) {
            $oldValue = $value;
            $value = array();
            foreach ($oldValue as $item) {
                $result = $this->checkDataType($field, $item);
                if ($result === FALSE) {
                    return FALSE;
                }
                $value[] = $result;
            }
        } else {
            $value = $this->checkDataType($field, $value);
        }
        return $value;
    }

    private function checkDataType(FormField $field, $value) {
        switch ($field->getDataType()) {
            case DataType::INT:
            case DataType::FLOAT:
                return $this->translateNumbericDataType($field, $value);
            case DataType::OBJECT:
                if (is_object($value)) {
                    return $value;
                }
                break;
            case DataType::ARRAY_TYPE:
                if (is_array($value)) {
                    return $value;
                }
                break;
            default:
                throw new \InvalidArgumentException('Undefined data type');
        }
        $this->set_Field_Value_Is_Invalid($field, $field->getErrorMessage());
        return FALSE;
    }

    private function translateNumbericDataType(FormField $field, $value) {
        if (! is_numeric($value)) {
            $this->set_Field_Value_Is_Invalid($field, $field->getErrorMessage());
            return FALSE;
        }
        if ($field->getDataType() == DataType::INT) {
            $intvalue = intval($value);
            if ($intvalue == $value) {
                return $intvalue;
            }
        } elseif ($field->getDataType() == DataType::FLOAT) {
            return floatval($value);
        }
        $this->set_Field_Value_Is_Invalid($field, $field->getErrorMessage());
        return FALSE;
    }

    private function validateFieldValue(FormField $field, $validator, $key = FALSE) {
        $value = $key === FALSE ? $field->getValue() : $field->getValue($key);
        try {
            if (is_array($validator)) {
                if ($validator[1]) {
                    $message = call_user_func($validator[0], $value);
                    if ($message) {
                        $this->set_Field_Value_Is_Invalid($field, $message);
                        return TRUE;
                    }
                } else {
                    $field->setValue(call_user_func($validator[0], $value), $key);
                }
            } else {
                if ($validator instanceof IValidator) {
                    if (! $validator->isValid($value)) {
                        $this->set_Field_Value_Is_Invalid($field, $validator->getMessages());
                        return TRUE;
                    }
                } else {
                    $field->setValue($validator->filter($value), $key);
                }
            }
        } catch (\Exception $e) {
            $this->set_Field_Value_Is_Invalid($field, $e->getMessage());
            return TRUE;
        }
        
        return FALSE;
    }

    private function dumpFormValue($form_fields, $class) {
        foreach ($form_fields as $field) {
            $key_name = $field->getKeyName();
            if ($key_name === FALSE) {
                continue;
            }
            $class->$key_name = $field->getValue();
        }
        return $class;
    }

    public function setFormErrorHint($form_hint) {
        $invalid = $this->getInvalidFields();
        if ($invalid) {
            foreach ($invalid as $field) {
                $key_name = $field->getKeyName();
                $form_hint->$key_name = $field->getErrorMessage();
            }
            return FALSE;
        } else {
            throw new FormException('Internal error in form component');
        }
    }

    public function makeFormDataHtmlSafe($form_fields, $form_data) {
        if (! is_array($form_fields)) {
            $form_fields = array($form_fields);
        }
        foreach ($form_fields as $field) {
            $data_type = $field->getDataType();
            if ($data_type == DataType::FILE || $data_type == DataType::FILE_ARRAY) {
                continue;
            }
            $key_name = $field->getKeyName();
            if ($key_name === FALSE) {
                continue;
            }
            $value = $form_data->$key_name;
            if ($field->isArray()) {
                if (is_array($value)) {
                    foreach ($value as $key => $item) {
                        $value[$key] = htmlspecialchars($item, ENT_QUOTES);
                    }
                }
            } else {
                $value = htmlspecialchars($value, ENT_QUOTES);
            }
            $form_data->$key_name = $value;
        }
    }
}

?>