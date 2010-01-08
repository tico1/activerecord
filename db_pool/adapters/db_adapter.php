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
 * Clase base para los adaptadores de Base de Datos
 * 
 * @category   Kumbia
 * @package    DbPool 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * @see TableMetadata
 **/
require CORE_PATH . 'libs/ActiveRecord/db_pool/table_meta_data.php';

abstract class DbAdapter
{
    /**
     * Nombre de conexion
     *
     * @var string
     **/
    protected $_connection;
    
    /**
     * Genera la descripcion de una tabla
     *
     * @param string $table tabla
     * @param string $schema schema
     * @return array
     **/
    abstract public function describe($table, $schema=NULL);

    /**
     * Constructor
     *
     * @param string $connection nombre de conexion en databases.ini
     **/
    public function __construct($connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Obtiene instancia de query en funcion de la conexion
     *
     * @param string $connection conexion a base de datos en databases.ini
     * @return DbAdapter
     * @throw KumbiaException
     **/
    public static function factory($connection=null)
    {
        // carga la conexion por defecto
        if (!$connection) {
            $connection = Config::get('config.application.database');
        }
    
        // lee la configuracion de base de datos
        $databases = Config::read('databases');
        
        if(!isset($databases[$connection])) {
            throw new KumbiaException("No existe la conexion $connection en databases.ini");
        }
    
        $database = $databases[$connection];
    
        // genera el nombre de clase
        $Class = ucfirst($database['type']) . 'Db';
    
        // si no existe la clase la carga
        if(!class_exists($Class, FALSE)) {
            // carga la clase
            require CORE_PATH . "libs/ActiveRecord/db_pool/adapters/{$database['type']}_db.php";
        }
        
        return new $Class($connection);
    }
    
    /**
     * Genera el objeto pdo para la conexion
     *
     * @param string $new forzar un nuevo objeto pdo y no el del pool de conexiones
     * @return PDO
     **/
    public function pdo($new=FALSE)
    {
        return DbPool::factory($this->_connection, $new);
    }
    
    /**
     * Genera la consulta sql concreta
     *
     * @param DbQuery $dbQuery
     * @return string
     **/
    public function query($dbQuery)
    {
        $sqlArray = $dbQuery->getSqlArray();
        
        // verifica si se indico una table
        if(!isset($sqlArray['table'])) {
            throw new KumbiaException("Debe indicar una tabla para efectuar la consulta");
        }
        
        if(isset($sqlArray['select'])) {
            return $this->_select($sqlArray);
            
        } elseif(isset($sqlArray['insert'])) {
            return $this->_insert($sqlArray);
            
        } elseif(isset($sqlArray['update'])) {
            return $this->_update($sqlArray);
            
        } elseif(isset($sqlArray['delete'])) {
            return $this->_delete($sqlArray);
            
        }
        
        return null;
    }
    
    /**
     * Genera una consulta sql SELECT
     *
     * @param array $sqlArray
     * @return string
     **/
    protected function _select($sqlArray)
    {
        // verifica si esta definido el eschema
        if(isset($sqlArray['schema'])) {
            $source = "{$sqlArray['schema']}.{$sqlArray['table']}";
        } else {
            $source = $sqlArray['table'];
        }
        
        $select = 'SELECT';
        if(isset($sqlArray['distinct']) && $sqlArray['distinct']) {
            $select .= ' DISTINCT';
        }
        
        return $this->_joinClausules($sqlArray, "$select {$sqlArray['select']} FROM $source");
    }
    
    /**
     * Genera una consulta sql INSERT
     *
     * @param array $sqlArray
     * @return string
     **/
    protected function _insert($sqlArray)
    {
        // alias para manejar mas facil
        $insert = $sqlArray['insert'];
        
        // si se paso array
        if(is_array($insert['columns'])) {
            // obtiene la conexion pdo
            $pdo = $this->pdo();
        
            $columns = array();
            $values = array();
            
            foreach($insert['columns'] as $k => $v) {
                $columns[] = $k;
                $values[] = $pdo->quote($v);
            }
            
            $columns = implode(', ', $columns);
            $values = implode(', ', $values);
        } else {
            $columns = $insert['columns'];
            $values = $insert['values'];
        }
        
        // verifica si esta definido el eschema
        if(isset($sqlArray['schema'])) {
            $source = "{$sqlArray['schema']}.{$sqlArray['table']}";
        } else {
            $source = $sqlArray['table'];
        }
        
        return "INSERT INTO $source ($columns) VALUES ($values)";
    }
    
    /**
     * Genera una consulta sql INSERT
     *
     * @param array $sqlArray
     * @return string
     **/
    protected function _update($sqlArray)
    {
        // si se paso array
        if(is_array($sqlArray['update'])) {
            // obtiene la conexion pdo
            $pdo = $this->pdo();
            
            $values = array();
            
            foreach($sqlArray['update'] as $k => $v) {
                $values[] = "$k=" . $pdo->quote($v);
            }
            
            $values = implode(', ', $values);
        } else {
            $values = $sqlArray['update'];
        }
        
        // verifica si esta definido el eschema
        if(isset($sqlArray['schema'])) {
            $source = "{$sqlArray['schema']}.{$sqlArray['table']}";
        } else {
            $source = $sqlArray['table'];
        }
        
        return $this->_joinClausules($sqlArray, "UPDATE $source SET $values");
    }
    
    /**
     * Genera una consulta sql DELETE
     *
     * @param array $sqlArray
     * @return string
     **/
    protected function _delete($sqlArray)
    {
        // verifica si esta definido el eschema
        if(isset($sqlArray['schema'])) {
            $source = "{$sqlArray['schema']}.{$sqlArray['table']}";
        } else {
            $source = $sqlArray['table'];
        }
        
        return $this->_joinClausules($sqlArray, "DELETE FROM $source");
    }
    
    /**
     * Une con las clausulas adicionales de consulta
     *
     * @param array $sqlArray array de condiciones
     * @param string $sql consulta sql donde se unira las clausulas
     * @return string
     **/
    protected function _joinClausules($sqlArray, $sql)
    {
        // para inner join
        if(isset($sqlArray['join'])) {
            foreach($sqlArray['join'] as $join) {
                $sql .= " INNER JOIN {$join['table']} ON ({$join['conditions']})";
            }
        }

        // para left outer join
        if(isset($sqlArray['leftJoin'])) {
            foreach($sqlArray['leftJoin'] as $join) {
                $sql .= " LEFT OUTER JOIN {$join['table']} ON ({$join['conditions']})";
            }
        }

        // para right outer join
        if(isset($sqlArray['rightJoin'])) {
            foreach($sqlArray['rightJoin'] as $join) {
                $sql .= " RIGHT OUTER JOIN {$join['table']} ON ({$join['conditions']})";
            }
        }

        // para full join
        if(isset($sqlArray['fullJoin'])) {
            foreach($sqlArray['fullJoin'] as $join) {
                $sql .= " FULL JOIN {$join['table']} ON ({$join['conditions']})";
            }
        }

        if(isset($sqlArray['where'])) {
            if(is_array($sqlArray['where'])) {
                // obtiene la conexion pdo
                $pdo = $this->pdo();
            
                // genera la condicion where partiendo de un array
                $where = array();
                foreach($sqlArray['where'] as $k => $v) {
                    $where[] = "$k=" . $pdo->quote($v);
                }
                $where = implode(' AND ', $where);
            } else {
                $where = $sqlArray['where'];
            }
            $sql .= " WHERE $where";
        }
    
        if(isset($sqlArray['group'])) {
            $sql .= " GROUP BY {$sqlArray['group']}";
        }

        if(isset($sqlArray['having'])) {
            $sql .= " HAVING {$sqlArray['having']}";
        }

        if(isset($sqlArray['order'])) {
            $sql .= " ORDER BY {$sqlArray['order']}";
        }

        if(isset($sqlArray['limit'])) {
            $sql .= " LIMIT {$sqlArray['limit']}";
        }

        if(isset($sqlArray['offset'])) {
            $sql .= " OFFSET {$sqlArray['offset']}";
        }
        
        return $sql;
    }
}