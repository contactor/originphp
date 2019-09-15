<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\db;

/**
 * Wrap PDO property for statment.
 * @author Johnson Tsang <contactor@gmail.com> 2015-07-08
 */
class SqlBuilder {
    
    /**
     * crud (create/retrive/update/delete)
     */
    private $_crud;
    
    /**
     * Table name. required.
     * eg: 'user'
     * @var string
     */
    private $_table_name;
    
    /**
     * Table class name. select SQL use only, required when need read result to the object
     * eg: 'ns\UserTable'
     * @var string
     */
    private $_table_class_name;
    
    /**
     * Table property class name. required
     * eg: 'ns\UserTable_P'
     * @var string
     */
    private $_table_property_name;
    
    /**
     * Bind data in SQL
     * Notice: bind data can be NULL, so only set it to FALSE means no bind data
     * @var object/object array/value/value array
     */
    private $_bind_data = FALSE;
    
    /**
     * Bind fields in SQL
     * @var string/string array
     */
    private $_bind_fields;
    
    /**
     * create/select(retrieve)/update fields
     * @var mixed(string or array. string seperated by ',', eg: "id,name")
     */
    private $_cru_fields;
    
    /**
     * Bind fields in where statment, used with $where_string
     * if $where_string is not set, the array items is field names with AND condition, or
     * if $where_string is set but contains parameters need bind fields, it's $where_fields
     * @var mixed(string or array. string seperated by ',', eg: "id,name")
     */
    private $_where_fields;
    
    /**
     * Where statment, used with $where_fields
     * if $where_string && $where_fields == FALSE/NULL, use primary key;
     * @var string
     */
    private $_where_sql;
    
    /**
     * Append to $_where_fields
     * @var mixed(string or array. string seperated by ',', eg: "id,name")
     */
    private $_where_patch_fields;
    
    /**
     * Append to $_where_sql
     * @var string
     */
    private $_where_patch_sql;
    
    /**
     * Where statment: if TRUE no where statment in SQL (operate all records)
     * select/update/delete all records. useless for insert 
     * @var boolean
     */
    private $_no_where;
    
    /**
     * Update fields
     * @var string
     */
    private $_update_sql;
    
    /**
     * Order by statment in select SQL 
     * @var string
     */
    private $_select_order_by;
    
    /**
     * Group by statment in select SQL 
     * @var string
     */
    private $_select_group_by;
    
    /**
     * Limit rows statment in select SQL
     * @var mixed(int or int array)
     */
    private $_select_limit_rows;
    
    /**
     * Lock statment in select SQL. eg 'for update'/'for share'
     * @var string
     */
    private $_select_for_lock;
    
    /**
     * Is the result has multiple rows in select statment  
     * @var boolean
     */
    private $_is_multiple_row_result;
    
    /**
     * Is return insert ID in insert statment  
     * @var boolean
     */
    private $_is_return_insert_id;
    
    /**
     * Is return affected row count in update/delete statment  
     * @var boolean
     */
    private $_is_return_affected_row_count;
    
    /**
     * Primary key field name  
     * @var string
     */
    private $_primary_key_field;
    
    /**
     * @var SqlExecutor
     */
    private $_sql_executor;

    /**
     * @param string $table_name
     * @param string $table_class_name
     * @param string $table_property_name
     */
    public function __construct($table_name, $table_class_name, $table_property_name) {
        if (is_string($table_name) && is_string($table_class_name) && is_string($table_property_name)) {
            $this->_table_name = $table_name;
            $this->_table_class_name = $table_class_name;
            $this->_table_property_name = $table_property_name;
            
            $this->_primary_key_field = $table_property_name::$PRIMARY_KEY_FIELD;
        } else {
            throw new \InvalidArgumentException('Invalid table name');
        }
    }

    /**
     * @param string/array $fields
     */
    public function select($fields = '*') {
        $this->_crud = SqlExecutor::SELECT;
        $this->_cru_fields = $fields;
        return $this;
    }

    /**
     * @param object $object
     * @param string/array $fields
     */
    public function selectObject($object, $fields = FALSE) {
        $this->_crud = SqlExecutor::SELECT;
        if ($fields) {
            $this->_cru_fields = $fields;
            return $this->object2RdFields($object);
        }
        return $this->object2RdFields($object, TRUE);
    }

    /**
     * @param string $field
     */
    public function count($field = FALSE) {
        $this->_crud = SqlExecutor::SELECT;
        return $this->setCountField($field);
    }

    /**
     * @param object $object
     * @param string $field
     */
    public function countObject($object, $field = FALSE) {
        $this->_crud = SqlExecutor::SELECT;
        $this->setCountField($field);
        return $this->object2RdFields($object);
    }

    /**
     * @param string/array $fields
     */
    public function insert($fields) {
        $this->_crud = SqlExecutor::INSERT;
        $this->_cru_fields = $fields;
        return $this;
    }

    /**
     * @param object/object array $object
     */
    public function insertObject($object) {
        $this->_crud = SqlExecutor::INSERT;
        return $this->object2CuFields($object, FALSE);
    }

    /**
     * @param string/array $fields
     * @param string $sql
     */
    public function update($fields, $sql = FALSE) {
        $this->_crud = SqlExecutor::UPDATE;
        $this->_cru_fields = $fields;
        $this->_update_sql = $sql;
        return $this;
    }

    /**
     * Attention: will return NULL if nothing need update
     * @param object/object array $object
     * @param object $ref_object
     * @return SqlBuilder or NULL if nothing need update
     */
    public final function updateObject($object, $ref_object = FALSE) {
        $this->_crud = SqlExecutor::UPDATE;
        $this->_update_sql = FALSE;
        if (is_object($ref_object)) {
            $fields = $this->retrieveUpdatedFields($object, $ref_object, $this->_primary_key_field);
            if (! $fields) {
                return NULL;
            }
            $this->_cru_fields = $fields;
            return $this->withData($object);
        }
        return $this->object2CuFields($object, TRUE);
    }

    public function delete() {
        $this->_crud = SqlExecutor::DELETE;
        return $this;
    }

    /**
     * @param object $object
     */
    public function deleteObject($object) {
        $this->_crud = SqlExecutor::DELETE;
        return $this->object2RdFields($object);
    }

    /**
     * @param string $sql
     */
    public function orderBy($sql) {
        $this->_select_order_by = $sql;
        return $this;
    }

    /**
     * @param string $sql
     */
    public function groupBy($sql) {
        $this->_select_group_by = $sql;
        return $this;
    }

    /**
     * @param int $count
     * @param int $offset
     */
    public function limit($count, $offset = FALSE) {
        $this->_select_limit_rows = is_numeric($offset) ? array($count, $offset) : $count;
        return $this;
    }

    /**
     * $lock=TRUE: equals 'FOR UPDATE"
     * @param boolean/string $lock
     */
    public function forLock($lock = TRUE) {
        if ($lock === TRUE) {
            $this->_select_for_lock = 'FOR UPDATE';
        } elseif (empty($lock)) {
            $this->_select_for_lock = FALSE;
        } elseif (is_string($lock)) {
            $this->_select_for_lock = $lock;
        } else {
            throw new \InvalidArgumentException('Invalid SQL lock');
        }
        return $this;
    }

    /**
     * @param boolean $yes
     */
    public function returnMultipleRowResult($yes = TRUE) {
        $this->_is_multiple_row_result = $yes;
        return $this;
    }

    /**
     * @param boolean $yes
     */
    public function returnInsertId($yes = TRUE) {
        $this->_is_return_insert_id = $yes;
        return $this;
    }

    /**
     * @param boolean $yes
     */
    public function returnAffectedRowCount($yes = TRUE) {
        $this->_is_return_affected_row_count = $yes;
        return $this;
    }

    /**
     * @param string $update_sql
     */
    public function setUpdateSql($update_sql) {
        $this->_update_sql = $update_sql;
        return $this;
    }

    /**
     * Set where SQL string and bind fields
     * Attention: bind fields will not change if $bind_fields === FALSE
     * @param string $where_sql
     * @param string/string array $bind_fields
     */
    public function where($where_sql, $bind_fields = FALSE) {
        $this->_where_sql = $where_sql;
        if ($bind_fields !== FALSE) {
            $this->_where_fields = $bind_fields;
        }
        $this->_no_where = FALSE;
        return $this;
    }

    /**
     * Append where SQL string and bind fields
     * @param string $where_sql
     * @param string/string array $bind_fields
     */
    public function appendWhere($where_sql, $bind_fields = FALSE) {
        if ($where_sql) {
            if (empty($this->_where_patch_sql)) {
                $this->_where_patch_sql = [$where_sql];
            } else {
                $this->_where_patch_sql[] = $where_sql;
            }
        }
        if ($bind_fields) {
            if (empty($this->_where_patch_fields)) {
                $this->_where_patch_fields = [$bind_fields];
            } else {
                $this->_where_patch_fields[] = $bind_fields;
            }
        }
        return $this;
    }

    /**
     * @param boolean $yes
     */
    public function noWhere($yes = TRUE) {
        $this->_no_where = $yes;
        if ($yes) {
            $this->_where_sql = FALSE;
            $this->_where_patch_sql = FALSE;
            $this->_where_fields = NULL;
            $this->_where_patch_fields = NULL;
        }
        return $this;
    }

    /**
     * @param string $bind_data
     * @param string $bind_fields
     */
    public function withData($bind_data, $bind_fields = FALSE) {
        $this->_bind_data = $bind_data;
        $this->_bind_fields = $bind_fields;
        return $this;
    }

    public function getCrud() {
        return $this->_crud;
    }

    public function setCrud($crud) {
        $this->_crud = $crud;
    }

    public function getTableName() {
        return $this->_table_name;
    }

    public function setTableName($table_name) {
        $this->_table_name = $table_name;
    }

    public function getTableClassName() {
        return $this->_table_class_name;
    }

    public function setTableClassName($table_class_name) {
        $this->_table_class_name = $table_class_name;
    }

    public function getTablePropertyName() {
        return $this->_table_property_name;
    }

    public function setTablePropertyName($table_property_name) {
        $this->_table_property_name = $table_property_name;
    }

    public function getBindData() {
        return $this->_bind_data;
    }

    public function setBindData($bind_data) {
        $this->_bind_data = $bind_data;
    }

    public function getBindFields() {
        return $this->_bind_fields;
    }

    public function setBindFields($bind_fields) {
        $this->_bind_fields = $bind_fields;
    }

    public function getCruFields() {
        return $this->_cru_fields;
    }

    public function setCruFields($cru_fields) {
        $this->_cru_fields = $cru_fields;
    }

    public function setCountField($field) {
        if ($field) {
            if (! is_string($field)) {
                throw new \InvalidArgumentException('Invalid count field');
            }
            $field = trim($field);
            if (! $field) {
                $field = $this->_primary_key_field;
            }
        } else {
            $field = $this->_primary_key_field;
        }
        $this->_cru_fields = 'count(' . $field . ')';
        return $this;
    }

    public function getWhereFields() {
        return $this->_where_fields;
    }

    public function setWhereFields($where_fields) {
        $this->_where_fields = $where_fields;
    }

    public function getWhereSql() {
        return $this->_where_sql;
    }

    public function setWhereSql($where_sql) {
        $this->_where_sql = $where_sql;
    }

    public function getWherePatchFields() {
        return $this->_where_patch_fields;
    }

    public function setWherePatchFields($where_fields) {
        $this->_where_patch_fields = $where_fields;
    }

    public function getWherePatchSql() {
        return $this->_where_patch_sql;
    }

    public function setWherePatchSql($where_sql) {
        $this->_where_patch_sql = $where_sql;
    }

    public function isNoWhere() {
        return $this->_no_where;
    }

    public function getUpdateSql() {
        return $this->_update_sql;
    }

    public function getSelectOrderBy() {
        return $this->_select_order_by;
    }

    public function getSelectGroupBy() {
        return $this->_select_group_by;
    }

    public function getSelectLimitRows() {
        return $this->_select_limit_rows;
    }

    public function setSelectLimitRows($select_limit_rows) {
        return $this->_select_limit_rows = $select_limit_rows;
    }

    public function getSelectForLock() {
        return $this->_select_for_lock;
    }

    public function setSelectForLock($select_for_lock) {
        $this->_select_for_lock = $select_for_lock;
    }

    public function isMultipleRowResult() {
        return $this->_is_multiple_row_result;
    }

    public function isReturnInsertId() {
        return $this->_is_return_insert_id;
    }

    public function isReturnAffectedRowCount() {
        return $this->_is_return_affected_row_count;
    }

    public function getPrimaryKeyField() {
        return $this->_primary_key_field;
    }

    public function setPrimaryKeyField($primary_key_field) {
        $this->_primary_key_field = $primary_key_field;
    }

    public function setSqlExecutor($sql_executor) {
        $this->_sql_executor = $sql_executor;
        return $this;
    }

    public function beginTransaction() {
        return $this->getSqlExecutor()->beginTransaction();
    }

    public function commit() {
        return $this->getSqlExecutor()->commit();
    }

    public function rollBack() {
        return $this->getSqlExecutor()->rollBack();
    }

    public function executeSql() {
        return $this->getSqlExecutor()->executeSql($this);
    }

    /**
     * @param string $sql
     * @param mixed $bind_data
     * @param string/string array $bind_fields
     * @param string/string array $int_fields
     * @return \PDOStatement
     */
    public function runManualSql($sql, $bind_data = FALSE, $bind_fields = FALSE, $int_fields = FALSE) {
        return $this->getSqlExecutor()->runManualSql($sql, $bind_data, $bind_fields, $int_fields);
    }

    private function getSqlExecutor() {
        if (! $this->_sql_executor) {
            throw new \LogicException('SQL executor not found yet');
        }
        return $this->_sql_executor;
    }

    /**
     * Retrieve all field names from object for SQL insert/update, but
     * exclude fields: primary ID field, field that has valid value
     * @param object/object array $object
     * @param bool $ignore_primary_key
     */
    private function object2CuFields($object, $ignore_primary_key) {
        $this->withData($object);
        $object2CuFields_func = function ($object) use($ignore_primary_key) {
            $all_vars = get_object_vars($object);
            if (! $all_vars) {
                throw new \InvalidArgumentException('Empty object for cu_fields');
            }
            $cu_fields = array();
            $primary_key_field = $this->_primary_key_field;
            foreach ($all_vars as $field => $value) {
                if ($this->isFieldHasNoData($value, $field) || $ignore_primary_key && $field == $primary_key_field) {
                    continue;
                }
                $cu_fields[] = $field;
            }
            if (empty($cu_fields)) {
                throw new \InvalidArgumentException('No data for fields');
            }
            $this->_cru_fields = $cu_fields;
        };
        if (is_object($object)) {
            $object2CuFields_func($object);
        } elseif (is_array($object) && isset($object[0]) && is_object($object[0])) {
            $object2CuFields_func($object[0]);
        } else {
            throw new \InvalidArgumentException('Invalid object for cu_fields');
        }
        return $this;
    }

    /**
     * Retrieve select field names and where field names from object for SQL select, or
     * Retrieve where field names for SQL delete. rules:
     * for select fields: value===TRUE, if no select fields, use '*'
     * for where fields: has valid value
     * @param object $object
     */
    private function object2RdFields($object, $set_r_fields = FALSE) {
        if (! is_object($object)) {
            throw new \InvalidArgumentException('Invalid object for r_fields');
        }
        $this->withData($object);
        $all_vars = get_object_vars($object);
        if (! $all_vars) {
            throw new \InvalidArgumentException('Empty object for r_fields');
        }
        if ($set_r_fields) {
            $r_fields = array();
        }
        $where_fields = array();
        foreach ($all_vars as $field => $value) {
            if ($set_r_fields && $value === TRUE) {
                $r_fields[] = $field;
                continue;
            }
            if ($this->isFieldHasNoData($value, $field)) {
                continue;
            }
            $where_fields[] = $field;
        }
        if ($set_r_fields) {
            $this->_cru_fields = $r_fields ? $r_fields : '*';
        }
        $this->where(FALSE, $where_fields);
        return $this;
    }

    /**
     * check if is a field has value, rules:
     * $value === NULL: no data, return FALSE
     * $value is string or numeric: has data, return TRUE
     * other cases: field has invalid data
     */
    private function isFieldHasNoData($value, $field) {
        if ($value === NULL) {
            return TRUE;
        }
        if (is_string($value) || is_numeric($value)) {
            return FALSE;
        }
        throw new \InvalidArgumentException('Invalid field value for field: ' . $field);
    }

    /**
     * Retrieve updated fields from updated object by compare to the reference object
     * @param object $ref_object
     * @param object $updated_object
     * @param bool $ignore_field
     */
    private function retrieveUpdatedFields($updated_object, $ref_object, $ignore_field = FALSE) {
        if (! is_object($updated_object)) {
            throw new \InvalidArgumentException('Invalid reference or updated object');
        }
        $fields = array();
        $vars = get_object_vars($updated_object);
        if ($vars) {
            foreach ($vars as $key => $value) {
                if ($key == $ignore_field) {
                    continue;
                }
                if (property_exists($ref_object, $key) && $ref_object->$key != $value) {
                    $fields[] = $key;
                }
            }
        }
        return $fields;
    }
}

?>