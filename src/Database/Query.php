<?php

namespace SimpleCheckout\Database;

class Query {
    /**
     * 
     * @var \PDO $connection
     */
    static $connection;
    static $cfgConnection;
    
    /**
     * 
     * @param string $dbname Nome do banco de dados
     * @param string $host Hostname do banco de dados
     * @param string $port Porta do banco DEFAULT=NULL (3306)
     * @param string $username Usuario do Banco
     * @param string $password Senha do usuario
     * @param string $driver Driver de accesso ao banco DEFAULT NULL (Somente: mysql)
     * @throws \Exception
     * @return boolean
     */
    static function setConnection($dbname,$host = NULL,$port=NULL,$username = NULL,$password = NULL,$driver = NULL){
        is_null($driver) && $driver = 'mysql';
        is_null($host) && $host = 'localhost';
        is_null($port) && $port = '3306';
        is_null($username) && $username = 'root';
        is_null($password) && $password = '';
        
        $driversAceitos = array('mysql');
        if(!in_array($driver,$driversAceitos)){
            throw new \Exception("Driver Driver '{$driver}' not accepted.");
            return false;
        }
        
        self::$cfgConnection = array(
            'driver'=>$driver,
            'host'=>$host,
            'port'=>$port,
            'dbname'=>$dbname,
            'username'=>$username,
            'password'=>$password
        );
        return true;
    }
    /**
     * 
     * @throws \Exception
     * @return \PDO|boolean
     */
    static public function connect(){
        if(isset(self::$connection)){
            return self::$connection;
        }
        
        if(is_null(self::$cfgConnection)){
            throw new \Exception("Banco de dados não configurado");
            return false;
        }
        
        $driver = self::$cfgConnection['driver'];
        $host = self::$cfgConnection['host'];
        $port = self::$cfgConnection['port'];
        $dbname = self::$cfgConnection['dbname'];
        $username = self::$cfgConnection['username'];
        $password = self::$cfgConnection['password'];
        
        $options = array();
        $options[\PDO::ATTR_PERSISTENT] = false;
        if($driver=="mysql"){
            //$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'UTF8'";
            $options[1002] = "SET NAMES 'UTF8'";
        }
        
        try {
            self::$connection = new \PDO(
                "{$driver}:host={$host};port={$port};dbname={$dbname};charset=UTF8",
                $username,
                $password,
                $options
            );
        } catch (\PDOException $e) {
            self::$connection = null;
            throw new \Exception("Connection failed: " . utf8_encode( $e->getMessage() ).".");
            return false;
        }
        
        return self::$connection;
    }
    /** Execulta query sql
     * 
     * @param string $sql
     * @throws \Exception
     * @return boolean|\PDOStatement
     */
    static public function query($sql){
        $pdo = self::connect();
        
        if(!$query = $pdo->query($sql)){
            list($handle, $codError, $StrError) = $pdo->errorInfo();
            throw new \Exception("Error: #{$codError}: {$StrError}",$codError);
            return false;
        }
        
        return $query;
    }
    static public function getData($table, $fields = null, $where = null){
        $query = "SELECT ".(is_null($fields)?'*':$fields)." FROM {$table}";
        if(!is_null($where)){
            $query .= " WHERE {$where}";
        }
        
        if($result = self::query($query)){
            if($result->rowCount()==1){
                return $result->fetch(\PDO::FETCH_ASSOC);
            } elseif($result->rowCount()>1){
                return $result->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    static public function insertByArray($array,$table){
        $fields = "";
        $values = "";
        $sep = "";
        foreach ($array as $field=>$value){
            $fields .= "{$sep}`{$field}`";
            if( is_null($value) ){
                $values .= "{$sep}NULL";
            } else {
                $values .= "{$sep}'{$value}'";
            }
            $sep = ",";
        }
        
        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$values})";
        self::query($sql);
        
        return self::lastInsertId($instanceName);
    }
    /**
     * 
     * @param array $array
     * @param string $table
     * @param string $where
     * @return boolean|PDOStatement
     */
    static public function updateByArray($array,$table,$where){
        $values = "";
        $sep = "";
        foreach ($array as $field=>$value){
            $values .= "{$sep}`{$field}` = ";
            if( is_null($value) ){
                $values .= "NULL";
            } else {
                $values .= "'{$value}'";
            }
            $sep = ",";
        }
        
        $sql = "UPDATE `{$table}` SET {$values} WHERE {$where}";
        return self::query($sql);
    }
    static public function lastInsertId($name=null){
        $pdo = self::$connection;
        return $pdo->lastInsertId($name);
    }
}