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
 * Tricky: only for code assitance, to exclude some functions in SqlBuilder.
 * eg: @return ISqlBuilder
 * @author Johnson Tsang <contactor@gmail.com> 2015-07-08
 */
interface ISqlBuilder {

    /**
     * @param string $sql
     * @return ISqlBuilder
     */
    public function orderBy($sql);

    /**
     * @param string $sql
     * @return ISqlBuilder
     */
    public function groupBy($sql);

    /**
     * @param int $count
     * @param int $offset
     * @return ISqlBuilder
     */
    public function limit($count, $offset = FALSE);

    /**
     * $lock=TRUE: equals 'FOR UPDATE"
     * @param boolean/string $lock
     * @return ISqlBuilder
     */
    public function forLock($lock = TRUE);

    /**
     * @param boolean $yes
     * @return ISqlBuilder
     */
    public function returnMultipleRowResult($yes = TRUE);

    /**
     * @param boolean $yes
     * @return ISqlBuilder
     */
    public function returnInsertId($yes = TRUE);

    /**
     * @param boolean $yes
     * @return ISqlBuilder
     */
    public function returnAffectedRowCount($yes = TRUE);

    /**
     * @param string $update_sql
     * @return ISqlBuilder
     */
    public function setUpdateSql($update_sql);

    /**
     * Set where SQL string and bind fields
     * Attention: bind fields will not change if $bind_fields === FALSE
     * @param string $where_sql
     * @param string/string array $bind_fields
     * @return ISqlBuilder
     */
    public function where($where_sql, $bind_fields = FALSE);

    /**
     * Append where SQL string and bind fields
     * @param string $where_sql
     * @param string/string array $bind_fields
     * @return ISqlBuilder
     */
    public function appendWhere($where_sql, $bind_fields = FALSE);

    /**
     * @param boolean $yes
     * @return ISqlBuilder
     */
    public function noWhere($yes = TRUE);

    /**
     * @param string $bind_data
     * @param string $bind_fields
     * @return ISqlBuilder
     */
    public function withData($bind_data, $bind_fields = FALSE);

    public function executeSql();
}

?>