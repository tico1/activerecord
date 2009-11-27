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
 * Clase que maneja el pool de conexiones
 * 
 * @category   Kumbia
 * @package    DbPool 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * @see DbQuery
 **/
require CORE_PATH . 'libs/db_pool/db_query.php';

/**
 * Clase que maneja el pool de conexiones
 *
 */
class DbPool
{
    /**
     * Singleton de conexiones a base de datos
     *
     * @var array
     **/
    protected static $_connections = array();

    /**
     * Realiza una conexión directa al motor de base de datos
     * usando el driver de Kumbia
     *
     * @param string $connection conexion a la base de datos en databases.ini
     * @param boolean $new nueva conexion
     * @return db
     */
    public static function factory($connection=null, $new=false)
    {
        // carga la conexion por defecto
        if (!$connection) {
            $connection = Config::get('config.application.database');
        }
        
        $databases = Config::read('databases');
        $config = $databases[$connection];
        
        // carga los valores por defecto para la conexion
        if (! isset($config['port'])) {
            $config['port'] = 0;
        }
        if (! isset($config['dsn'])) {
            $config['dsn'] = '';
        }
        if (! isset($config['host'])) {
            $config['host'] = '';
        }
        if (! isset($config['username'])) {
            $config['username'] = '';
        }
        if (! isset($config['password'])) {
            $config['password'] = '';
        }
        
        //Si no es una conexion nueva y existe la conexion singleton
        if (! $new && isset(self::$_connections[$connection])) {
            return self::$_connections[$connection];
        }
        
        // conecta con pdo
        $pdo = new PDO($config['type'] . ":" . $config['dsn'], $config['username'], $config['password']);
        
        //Si no es para conexion nueva, la cargo en el singleton
        if (! $new) {
            self::$_connections[$connection] = $pdo;
        }
        
        return $pdo;
    }
}