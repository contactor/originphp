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
 * Wrap the process of PDO executing SQL.
 * @author Johnson Tsang <contactor@gmail.com> 2015-07-08
 */
class SqlExecutor {
    /**
     * database types
     */
    const MYSQL = 'mysql';
    const POSTGRESQL = 'pgsql';
    
    /**
     * crud
     */
    const INSERT = 1;
    const SELECT = 2;
    const UPDATE = 3;
    const DELETE = 4;
    
    /**
     * database type
     */
    private $_database_type;
    
    /**
     * @var \PDO
     */
    private $_pdo;
    
    /**
     * @var bool
     */
    private $_is_in_transaction = FALSE;
    
    /**
     * Last SQL statment
     */
    private $_last_sql = '';
    
    /**
     * Is caceh statment
     */
    private $_statment_cache;
    
    /**
     * Exception Logger
     */
    private $_e_logger;
    
    /**
     * SQL Logger
     */
    private $_sql_logger;
    private $_sql_log_trid;
    
    /**
     * SQL statement count in latest transaction
     */
    private $_transaction_sql_count = 0;
    
    /**
     * INSERT/UPDATE/DELETE SQL statement count in latest transaction
     */
    private $_transaction_cud_sql_count = 0;

    /**
     * @param \PDO $pdo
     * @param string $db_type
     */
    public function __construct($pdo = NULL, $database_type = self::MYSQL) {
        $this->_pdo = $pdo;
        $this->_database_type = $database_type;
        $this->isCacheStatment(TRUE);
    }

    /**
     * Set PDO object
     * @param \PDO $dbo
     */
    public function setDbo(\PDO $pdo) {
        $this->_pdo = $pdo;
    }

    /**
     * Set database type
     * @param string $db_type
     */
    public function setDatabaseType($database_type) {
        $this->_database_type = $database_type;
    }

    /**
     * Set is cache statment
     * @param boolean $is_cache_statment
     */
    public function isCacheStatment($is_cache_statment) {
        $this->_statment_cache = $is_cache_statment ? new StatmentCache() : NULL;
    }

    /**
     * Set Exception logger. If logger is empty will not write log
     * @param callable $logger
     */
    public function setExceptionLogger($logger) {
        $this->_e_logger = $logger;
    }

    /**
     * Set SQL logger. If logger is empty will not write log
     * @param callable $logger
     */
    public function setSqlLogger($logger) {
        $this->_sql_logger = $logger;
    }

    /**
     * Get last SQL statment
     * @return string
     */
    public function getLastSql() {
        return $this->_last_sql;
    }

    /**
     * Get SQL statement count in latest transaction
     * @return int
     */
    public function getTransactionSqlCount() {
        return $this->_transaction_sql_count;
    }

    /**
     * Get INSERT/UPDATE/DELETE SQL statement count in latest transaction
     * @return int
     */
    public function getTransactionCudSqlCount() {
        return $this->_transaction_cud_sql_count;
    }

    /**
     * is in transaction
     * @return bool
     */
    public function isInTransaction() {
        return $this->_is_in_transaction;
    }

    /**
     * begin transaction
     * @return boolean
     */
    public function beginTransaction() {
        return $this->wrapExecuteSql(
            function () {
                if (! $this->_is_in_transaction) {
                    if ($this->_sql_logger) {
                        call_user_func($this->_sql_logger, 'beginTransaction', '----->');
                    }
                    $result = $this->_pdo->beginTransaction();
                    $this->_is_in_transaction = TRUE;
                    $this->_transaction_sql_count = 0;
                    $this->_transaction_cud_sql_count = 0;
                    return $result;
                }
            });
    }

    /**
     * commit transaction
     * @return boolean
     */
    public function commit() {
        return $this->wrapExecuteSql(
            function () {
                if ($this->_is_in_transaction) {
                    if ($this->_sql_logger) {
                        call_user_func($this->_sql_logger, 'commit', '<-----');
                    }
                    $result = $this->_pdo->commit();
                    $this->_is_in_transaction = FALSE;
                    $this->_transaction_sql_count = 0;
                    $this->_transaction_cud_sql_count = 0;
                    return $result;
                }
            });
    }

    /**
     * rollback transaction 
     * @return boolean
     */
    public function rollBack() {
        return $this->wrapExecuteSql(
            function () {
                if ($this->_is_in_transaction) {
                    if ($this->_sql_logger) {
                        call_user_func($this->_sql_logger, 'rollBack', '<-----');
                    }
                    $result = $this->_pdo->rollBack();
                    $this->_is_in_transaction = FALSE;
                    $this->_transaction_sql_count = 0;
                    $this->_transaction_cud_sql_count = 0;
                    return $result;
                }
            });
    }

    /**
     * A special function, for get postgresql sequence
     * @param string $sequenceName
     * @return integer
     */
    public function nextSequenceValue($sequenceName) {
        return $this->wrapExecuteSql(
            function () use($sequenceName) {
                $sql = "SELECT nextval('$sequenceName')";
                $stmt = $this->prepareSql($sql);
                try {
                    if (! $stmt->execute()) {
                        throw new \Exception('Execute SQL fail with no message');
                    }
                } catch (\Exception $e) {
                    if ($this->_sql_logger) {
                        call_user_func($this->_sql_logger, $e->getMessage(), $this->_sql_log_trid);
                    }
                    throw $e;
                }
                $id = $stmt->fetchColumn();
                if ($id) {
                    return $id;
                }
                throw new \UnexpectedValueException('Get invalid sequence ID.');
            });
    }

    /**
     * Execute SQL
     * @param SqlBuilder $sb
     * @return mixed
     */
    public function executeSql(SqlBuilder $sb) {
        return $this->wrapExecuteSql(
            function () use($sb) {
                $this->checkSqlString($sb);
                $this->checkBindFields($sb);
                $this->checkBindDataType($sb);
                $this->checkBindData($sb->getBindData());
                $this->checkCruFields($sb);
                $this->checkWhere($sb);
                $this->checkSelectLimitRows($sb);
                $this->buildSql($sb);
                return $this->queryDatabase($sb->sql, $sb->getBindData(), $sb->getBindFields(), $sb);
            });
    }

    /**
     * Manual execute SQL
     * @param string $sql
     * @param mixed $bind_data
     * @param string/string array $bind_fields
     * @param string/string array $int_fields
     * @return mixed
     */
    public function runManualSql($sql, $bind_data = FALSE, $bind_fields = FALSE, $int_fields = FALSE) {
        if (! is_string($sql)) {
            throw new \InvalidArgumentException('Invalid SQL string');
        }
        $sql = trim($sql);
        if (! $sql) {
            throw new \InvalidArgumentException('SQL string is empty');
        }
        return $this->wrapExecuteSql(
            function () use($sql, $bind_data, $bind_fields, $int_fields) {
                $bind_data = $this->checkBindDataType($bind_data);
                $this->checkBindData($bind_data);
                $bind_fields = $this->regulateFieldsToArray($bind_fields);
                $int_fields = $this->regulateFieldsToArray($int_fields);
                return $this->queryDatabase($sql, $bind_data, $bind_fields, $int_fields);
            });
    }

    private function wrapExecuteSql(callable $func) {
        try {
            return $func();
        } catch (\Exception $e) {
            if ($this->_e_logger) {
                call_user_func($this->_e_logger, $e, $this->_sql_log_trid);
            }
            throw $e;
        }
    }

    private function buildSql(SqlBuilder $sb) {
        $cru_fields = $sb->getCruFields();
        $where_fields = $sb->getWhereFields();
        $sql_where = $sb->getWhereSql();
        switch ($sb->getCrud()) {
            case self::INSERT:
                $bind_fields = $cru_fields;
                array_walk($cru_fields, function (&$value, $key) {
                    $value = ':' . $value;
                });
                $sql = 'INSERT INTO ' . $sb->getTableName() . ' (' . implode(',', $bind_fields) . ') VALUES(' . implode(',', $cru_fields) . ')';
                break;
            case self::SELECT:
                if (count($cru_fields) == 1) {
                    $cru_fields = $cru_fields[0];
                    if ($cru_fields != '*') {
                        $is_select_one_field = TRUE;
                    }
                } else {
                    $cru_fields = implode(',', $cru_fields);
                }
                $select_group_by = $sb->getSelectGroupBy() ? ' GROUP BY ' . $sb->getSelectGroupBy() : '';
                $select_order_by = $sb->getSelectOrderBy() ? ' ORDER BY ' . $sb->getSelectOrderBy() : '';
                $for_update = $sb->getSelectForLock() ? ' ' . $sb->getSelectForLock() : '';
                $sql = 'SELECT ' . $cru_fields . ' FROM ' . $sb->getTableName() . $sql_where . $select_group_by . $select_order_by . $sb->getSelectLimitRows() . $for_update;
                $bind_fields = $where_fields;
                break;
            case self::UPDATE:
                $update_string = $sb->getUpdateSql();
                $bind_fields = array_unique(array_merge($cru_fields, $where_fields));
                if (empty($update_string)) {
                    array_walk($cru_fields, function (&$value, $key) {
                        $value = $value . '=:' . $value;
                    });
                    $update_string = implode(',', $cru_fields);
                }
                $sql = 'UPDATE ' . $sb->getTableName() . ' SET ' . $update_string . $sql_where;
                break;
            case self::DELETE:
                $sql = 'DELETE FROM ' . $sb->getTableName() . $sql_where;
                $bind_fields = $where_fields;
                break;
            default:
                throw new \InvalidArgumentException('Invalid SQL CRUD');
        }
        $sb->setBindFields($bind_fields);
        $sb->sql = $sql;
        $sb->is_select_multi_fields = empty($is_select_one_field);
    }

    private function queryDatabase($sql, $bind_data, $bind_fields, $sb_OR_int_fields) {
        if ($bind_fields && ($bind_data === FALSE || is_array($bind_data) && empty($bind_data))) {
            throw new \InvalidArgumentException('SQL fileds and data not match');
        }
        
        $stmt = $this->prepareSql($sql, $sb_OR_int_fields);
        if (is_array($bind_data)) {
            $result = array();
            foreach ($bind_data as $one_obj) {
                $result[] = $this->executeStmt($sb_OR_int_fields, $stmt, $one_obj, $bind_fields);
            }
            return $result;
        } else {
            return $this->executeStmt($sb_OR_int_fields, $stmt, $bind_data, $bind_fields);
        }
    }

    private function executeStmt($sb_OR_int_fields, $stmt, $bind_value, $bind_fields) {
        $is_sql_builder = $sb_OR_int_fields instanceof SqlBuilder;
        $logger = $this->_sql_logger;
        if ($logger) {
            $log_values = array();
            $formatBindValue4Log_func = function ($the_value) {
                if ($the_value === NULL) {
                    return 'NULL';
                }
                if ($the_value === FALSE) {
                    return 'FALSE';
                }
                if ($the_value === '') {
                    return "''";
                }
                return $the_value;
            };
        }
        if ($bind_fields) {
            if ($is_sql_builder) {
                $table_property_name = $sb_OR_int_fields->getTablePropertyName();
                $int_fields = empty($table_property_name) || empty($table_property_name::$INT_FIELDS) ? array() : $table_property_name::$INT_FIELDS;
            } else {
                $int_fields = $sb_OR_int_fields;
            }
            foreach ($bind_fields as $field) {
                $param_type = in_array($field, $int_fields) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                if (is_object($bind_value)) {
                    $single_value = $bind_value->$field;
                    if ($logger) {
                        $log_values[] = $field . '=' . $formatBindValue4Log_func($bind_value->$field);
                    }
                    $stmt->bindParam(':' . $field, $bind_value->$field, $param_type);
                } else {
                    $single_value = $bind_value;
                    if ($logger) {
                        $log_values[] .= $field . '=' . $formatBindValue4Log_func($bind_value);
                    }
                    $stmt->bindParam(':' . $field, $bind_value, $param_type);
                }
                if ($this->isInvalidFieldValue($single_value)) {
                    throw new \InvalidArgumentException('Invalid field value for field: ' . $field);
                }
            }
        }
        if ($logger && $log_values) {
            call_user_func($logger, implode('; ', $log_values), $this->_sql_log_trid);
        }
        try {
            if (! $stmt->execute()) {
                throw new \Exception('Execute SQL fail with no message');
            }
        } catch (\Exception $e) {
            if ($logger) {
                call_user_func($logger, $e->getMessage(), $this->_sql_log_trid);
            }
            throw $e;
        }
        if (! $is_sql_builder) {
            return $stmt;
        }
        
        $sb = $sb_OR_int_fields;
        $table_class_name = $sb->getTableClassName();
        switch ($sb->getCrud()) {
            case self::INSERT:
                if ($sb->isReturnInsertId()) {
                    return $this->_pdo->lastInsertId();
                }
                return FALSE;
            case self::SELECT:
                if ($sb->is_select_multi_fields) {
                    return empty($sb->isMultipleRowResult()) ? $stmt->fetchObject($table_class_name) : $stmt->fetchAll(\PDO::FETCH_CLASS, $table_class_name);
                }
                if ($sb->isMultipleRowResult()) {
                    return $stmt->fetchAll(\PDO::FETCH_COLUMN);
                }
                return $stmt->fetchColumn();
            case self::UPDATE:
            case self::DELETE:
                if ($sb->isReturnAffectedRowCount()) {
                    return $stmt->rowCount();
                }
                return FALSE;
            default:
                throw new \InvalidArgumentException('Invalid SQL CRUD');
        }
    }

    private function getPrimaryKeyField(SqlBuilder $sb) {
        $table_property_name = $sb->table_property_name;
        if (empty($table_property_name)) {
            throw new \InvalidArgumentException('Table property parameter not set yet');
        }
        return $table_property_name::$PRIMARY_KEY_FIELD;
    }

    private function regulateFieldsToArray($fields, $is_select_fields = FALSE) {
        if (empty($fields)) {
            return array();
        }
        if (is_string($fields)) {
            $fields = trim($fields);
            if (empty($fields)) {
                return array();
            }
            $fields = explode(',', $fields);
        } elseif (is_array($fields)) {
            array_walk($fields, 
                function ($field, $key) {
                    if (! is_string($field)) {
                        throw new \InvalidArgumentException('Invalid SQL field name');
                    }
                });
        } else {
            throw new \InvalidArgumentException('Invalid SQL fields array');
        }
        $t_array = array();
        foreach ($fields as $field) {
            $field = trim($field);
            if ($field) {
                if ($this->isInvalidFieldName($field, $is_select_fields)) {
                    throw new \InvalidArgumentException('Invalid SQL field name: ' . $field);
                }
                $t_array[] = $field;
            }
        }
        return array_unique($t_array);
    }

    private function isInvalidFieldName($field, $is_select_fields = FALSE) {
        if ($is_select_fields) {
            if ($field == '*') {
                return FALSE;
            }
            return ! preg_match('/^[a-zA-Z][a-zA-Z0-9_()\s]*$/', $field); // field name could be function, eg: select version()
        }
        return ! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $field);
    }

    private function isInvalidFieldValue($value) {
        return (is_string($value) || is_numeric($value) || $value === NULL) ? FALSE : TRUE;
    }

    private function checkSqlString(SqlBuilder $sb) {
        $check_func = function ($sql, $error_msg) {
            if (empty($sql)) {
                return FALSE;
            }
            if (is_string($sql)) {
                return trim($sql);
            }
            throw new \InvalidArgumentException($error_msg);
        };
        $sb->setUpdateSql($check_func($sb->getUpdateSql(), 'Invalid SQL update string'));
        $sb->orderBy($check_func($sb->getSelectOrderBy(), 'Invalid SQL order by'));
        $sb->groupBy($check_func($sb->getSelectGroupBy(), 'Invalid SQL group by'));
        $sb->setSelectForLock($check_func($sb->getSelectForLock(), 'Invalid SQL for lock'));
        $sb->setWhereSql($check_func($sb->getWhereSql(), 'Invalid SQL where string'));
    }

    private function checkBindFields(SqlBuilder $sb) {
        $bind_fields = $this->regulateFieldsToArray($sb->getBindFields());
        $count = count($bind_fields);
        if ($count < 2) {
            return;
        }
        $bind_data = $sb->getBindData();
        if (! is_array($bind_data) || $count != count($bind_data)) {
            throw new \InvalidArgumentException('SQL bind_data not match bind_fields');
        }
        array_walk($bind_data, 
            function ($value, $key) {
                if (is_object($value) || is_array($value)) {
                    throw new \InvalidArgumentException('Invalid SQL non-object array data for bind_fields');
                }
            });
        
        $obj = new \stdClass();
        $key = 0;
        foreach ($bind_data as $value) {
            $bind_field = $bind_fields[$key];
            $obj->$bind_field = $value;
            $key ++;
        }
        $sb->setBindData($obj);
    }

    private function checkBindDataType($bind_data) {
        if ($bind_data instanceof SqlBuilder) {
            $sb = $bind_data;
            $bind_data = $sb->getBindData();
        }
        if (! (is_object($bind_data) || is_array($bind_data) || $bind_data === FALSE || ! $this->isInvalidFieldValue($bind_data))) {
            throw new \InvalidArgumentException('Invlid bind_data');
        }
        if (empty($sb)) {
            return $bind_data;
        }
        $sb->setBindData($bind_data);
    }

    private function checkBindData($bind_data) {
        if (! is_array($bind_data)) {
            return;
        }
        if (! isset($bind_data[0])) {
            $sort_data = $bind_data;
            $bind_data = array();
            foreach ($sort_data as $one_data) {
                $bind_data[] = $one_data;
            }
        }
        if (is_object($bind_data[0])) {
            array_walk($bind_data, 
                function ($obj, $key) {
                    if (! is_object($obj)) {
                        throw new \InvalidArgumentException('Invalid SQL object array');
                    }
                });
        } else {
            array_walk($bind_data, 
                function ($value, $key) {
                    if ($this->isInvalidFieldValue($value)) {
                        throw new \InvalidArgumentException('Invalid SQL non-object array');
                    }
                });
        }
    }

    private function checkCruFields(SqlBuilder $sb) {
        $crud = $sb->getCrud();
        if ($crud == self::DELETE) {
            return;
        }
        $is_select_fields = $crud == self::SELECT;
        $cru_fields = $this->regulateFieldsToArray($sb->getCruFields(), $is_select_fields);
        if (empty($cru_fields)) {
            if ($is_select_fields) {
                $cru_fields = array('*');
            } elseif ($crud == self::UPDATE && $sb->getUpdateSql()) {
            } else {
                throw new \InvalidArgumentException('SQL operation fields needed');
            }
        }
        $sb->setCruFields($cru_fields);
    }

    private function checkWhere(SqlBuilder $sb) {
        if ($sb->getCrud() == self::INSERT) {
            return;
        }
        $this->checkWhereMain($sb);
        $this->checkWherePatch($sb);
        $this->buildWhere($sb);
    }

    private function checkWhereMain(SqlBuilder $sb) {
        $where_fields = $this->regulateFieldsToArray($sb->getWhereFields());
        $sb->setWhereFields($where_fields);
        if ($sb->getWhereSql()) {
            return;
        }
        if ($where_fields) {
            $t_where_fields = $where_fields;
            array_walk($t_where_fields, function (&$value, $key) {
                $value = $value . '=:' . $value;
            });
            $sb->setWhereSql(implode(' AND ', $t_where_fields));
        } else {
            $sb->setWhereSql('');
        }
    }

    private function checkWherePatch(SqlBuilder $sb) {
        $patch_sql = $sb->getWherePatchSql();
        if ($patch_sql) {
            if (! is_array($patch_sql)) {
                throw new \InvalidArgumentException('Invalid where patch SQL');
            }
            $sb->setWherePatchSql(implode(' ', $patch_sql));
        }
        $patch_fields = $sb->getWherePatchFields();
        if ($patch_fields) {
            if (! is_array($patch_fields)) {
                throw new \InvalidArgumentException('Invalid where patch fields');
            }
            $where_fields = array();
            foreach ($patch_fields as $value) {
                $one_fields = $this->regulateFieldsToArray($value);
                if ($one_fields) {
                    $where_fields = array_merge($where_fields, $one_fields);
                }
            }
            $sb->setWherePatchFields($where_fields);
        }
    }

    private function buildWhere(SqlBuilder $sb) {
        $where_sql = $sb->getWhereSql();
        if ($sb->getWherePatchSql()) {
            $where_sql = trim($where_sql . ' ' . $sb->getWherePatchSql());
            $sb->setWhereSql($where_sql);
        }
        $where_fields = $sb->getWhereFields();
        if ($sb->getWherePatchFields()) {
            $where_fields = array_unique(array_merge($where_fields, $sb->getWherePatchFields()));
            $sb->setWhereFields($where_fields);
        }
        
        if ($sb->isNoWhere()) {
            if ($where_fields || $where_sql) {
                throw new \InvalidArgumentException('SQL where no/yes conflict');
            }
            $sb->setWhereSql('');
            return;
        }
        if ($where_sql) {
            $sb->setWhereSql(' WHERE ' . $where_sql);
            return;
        }
        if ($where_fields) {
            throw new \InvalidArgumentException('SQL where bind fields without SQL string');
        }
        if ($sb->getBindData() === FALSE) {
            if ($sb->getCrud() == self::SELECT) {
                $sb->setWhereSql('');
                return;
            }
            throw new \InvalidArgumentException('SQL where not found');
        }
        $primary_key_field = $sb->getPrimaryKeyField();
        $sql_where = ' WHERE ' . $primary_key_field . '=:' . $primary_key_field;
        $sb->setWhereSql($sql_where);
        $sb->setWhereFields([$primary_key_field]);
    }

    private function checkSelectLimitRows(SqlBuilder $sb) {
        if ($sb->getCrud() != self::SELECT) {
            return;
        }
        $select_limit_rows = $sb->getSelectLimitRows();
        if (empty($select_limit_rows)) {
            $sb->setSelectLimitRows('');
            return;
        }
        
        $checkLimitNumber_func = function ($select_limit_rows, $key = FALSE) {
            if (! is_numeric($select_limit_rows) || ! preg_match('/^[0-9]+$/', $select_limit_rows)) {
                throw new \InvalidArgumentException('Invalid SQL limit number: ' . $select_limit_rows);
            }
        };
        if (is_array($select_limit_rows)) {
            array_walk($select_limit_rows, $checkLimitNumber_func);
        } else {
            $checkLimitNumber_func($select_limit_rows);
        }
        
        switch ($this->_database_type) {
            case self::MYSQL:
                $limit_sql = (is_array($select_limit_rows) ? $select_limit_rows[0] . ',' . $select_limit_rows[1] : $select_limit_rows);
                break;
            case self::POSTGRESQL:
                $limit_sql = (is_array($select_limit_rows) ? $select_limit_rows[0] . ' OFFSET ' . $select_limit_rows[1] : $select_limit_rows);
                break;
            default:
                throw new \InvalidArgumentException('Invalid database type');
        }
        $sb->setSelectLimitRows(' LIMIT ' . $limit_sql);
    }

    private function generateRandomString() {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 6;
        $chars_len = strlen($chars) - 1;
        $output = '';
        for ($i = 0; $i < $length; $i ++) {
            $output .= substr($chars, mt_rand(0, $chars_len), 1);
        }
        return $output;
    }

    private function prepareSql($sql, $sb_OR_int_fields) {
        $this->_last_sql = $sql;
        
        $log_sql = $sql;
        if (! is_object($sb_OR_int_fields)) {
            $log_sql .= ' (manual)';
        }
        $cache = $this->_statment_cache;
        if ($cache) {
            $stmt = $cache->retrieveStatment($sql);
            if ($stmt) {
                $log_sql .= ' (cached)';
            }
        }
        // log SQL first, if next step, prepare statment error, can check the log to find the error SQL
        if ($this->_sql_logger) {
            $trid = $this->generateRandomString();
            $this->_sql_log_trid = $trid;
            call_user_func($this->_sql_logger, $log_sql, $trid);
        }
        if (! $stmt) {
            $stmt = $this->_pdo->prepare($sql);
            if ($cache) {
                $cache->appendStatment($stmt, $sql);
            }
        }
        if ($this->_is_in_transaction) {
            $this->_transaction_sql_count ++;
            if (strtoupper(substr($sql, 0, 6)) != 'SELECT') {
                $this->_transaction_cud_sql_count ++;
            }
        }
        return $stmt;
    }
}

?>
