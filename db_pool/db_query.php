<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * Clase para consultas SQL
 * 
 * @category   Kumbia
 * @package    DbPool 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

class DbQuery
{
    /**
     * Partes de la consulta sql
     *
     * @var array
     **/
    protected $_sql = array();

    /**
     * Clausula DISTINCT
     *
     * @param boolean $distinct
     * @return DbQuery
     **/
    public function distinct($distinct) 
    {
        $this->_sql['distinct'] = $distinct;
        return $this;
    }

    /**
     * Clausula WHERE
     *
     * @param string | array $conditions condiciones
     * @return DbQuery
     **/
    public function where($conditions) 
    {
        $where = array();
        if(is_array($conditions)){
            foreach ($conditions as $k => $v) {
            	$this->_sql['params'][] = $v;
            	$where[] = $k;
            }
        }
        $this->_sql['where'] = $where;
        return $this;
    }
    /**
     * Parametros que seran enlazados a la setencia SQL
     * 
     * @return Array
     */
    public function params()
    {
        if(isset($this->_sql['params'])){
            return $this->_sql['params'];
        }
        return NULL;
    }
    
    /**
     * Clausula INNER JOIN
     *
     * @param string $table nombre de tabla
     * @param string $conditions condiciones
     * @return DbQuery
     **/
    public function join($table, $conditions) 
    {
        $this->_sql['join'][] = array('table' => $table, 'conditions' => $conditions);
        return $this;
    }
    
    /**
     * Clausula LEFT OUTER JOIN
     *
     * @param string $table nombre de tabla
     * @param string $conditions condiciones
     * @return DbQuery
     **/
    public function leftJoin($table, $conditions) 
    {
        $this->_sql['leftJoin'][] = array('table' => $table, 'conditions' => $conditions);
        return $this;
    }
    
    /**
     * Clausula RIGHT OUTER JOIN
     *
     * @param string $table nombre de tabla
     * @param string $conditions condiciones
     * @return DbQuery
     **/
    public function rightJoin($table, $conditions) 
    {
        $this->_sql['rightJoin'][] = array('table' => $table, 'conditions' => $conditions);
        return $this;
    }
    
    /**
     * Clausula FULL JOIN
     *
     * @param string $table nombre de tabla
     * @param string $conditions condiciones
     * @return DbQuery
     **/
    public function fullJoin($table, $conditions) 
    {
        $this->_sql['fullJoin'][] = array('table' => $table, 'conditions' => $conditions);
        return $this;
    }
    
    /**
     * Columnas de la consulta
     *
     * @param string $table nombre de tabla
     * @return DbQuery
     **/
    public function table($table) 
    {
        $this->_sql['table'] = $table;
        return $this;
    }

    /**
     * Columnas de la consulta
     *
     * @param string $schema schema donde se ubica la tabla
     * @return DbQuery
     **/
    public function schema($schema) 
    {
        $this->_sql['schema'] = $schema;
        return $this;
    }

    /**
     * Clausula SELECT
     *
     * @param string $criteria criterio de ordenamiento
     * @return DbQuery
     **/
    public function order($criteria) 
    {
        $this->_sql['order'] = $criteria;
        return $this;
    }
    
    /**
     * Clausula GROUP
     *
     * @param string $columns columnas
     * @return DbQuery
     **/
    public function group($columns) 
    {
        $this->_sql['group'] = $columns;
        return $this;
    }    

    /**
     * Clausula HAVING
     *
     * @param string $conditions condiciones
     * @return DbQuery
     **/
    public function having($conditions) 
    {
        $this->_sql['having'] = $conditions;
        return $this;
    }

    /**
     * Clausula LIMIT
     *
     * @param int $limit
     * @return DbQuery
     **/
    public function limit($limit) 
    {
        $this->_sql['limit'] = $limit;
        return $this;
    }   

    /**
     * Clausula OFFSET
     *
     * @param int $offset
     * @return DbQuery
     **/
    public function offset($offset) 
    {
        $this->_sql['offset'] = $offset;
        return $this;
    }  

    /**
     * Construye la consulta SELECT
     *
     * @param string $columns columnas
     * @return DbQuery
     **/
    public function select($columns='*') 
    {
        $this->_sql['select'] = $columns;
        return $this;
    }
    /**
     * Columnas a utilizar en el Query
     * @return DbQuery
     */
    public function columns($columns)
    {
        $this->select($columns);
        return $this;
    }
    /**
     * Construye la consulta DELETE
     *
     * @return DbQuery
     **/
    public function delete() 
    {
        $this->_sql['delete'] = TRUE;
        return $this;
    }
    
    /**
     * Construye la consulta UPDATE
     *
     * @param string | array $values claves/valores
     * @return DbQuery
     **/
    public function update($values) 
    {
        $this->_sql['update'] = $values;
        return $this;
    }
    
    /**
     * Construye la consulta UPDATE
     *
     * @param string | array $columns columnas, o array de claves/valores
     * @param string $values 
     * @return DbQuery
     **/
    public function insert($columns, $values=null) 
    {
        $this->_sql['insert'] = array('columns' => $columns, 'values' => $values);
        return $this;
    }
    
    /**
     * Obtiene el array base con las partes de la consulta SQL
     *
     * @return array
     **/
    public function getSqlArray()
    {
        return $this->_sql;
    }
}
