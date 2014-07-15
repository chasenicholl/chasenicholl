<?php
class Db
{
    
    public static function init($driver = "mysql")
    {
        $host = DB_HOST;
        $port = DB_PORT;
        $db   = DB_DB;
        $user = DB_USER;
        $pass = DB_PASS;
                
        if ($driver === 'mysql') {
        
            if (!DB_PERSISTENT) {
        
                $conn = Cache::get(md5("comet_db_layer"), "file");
                if (empty($conn)) {
                    $conn = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
                    Cache::add(md5("comet_db_layer"), $conn, "file");
                }
                
            }
            else {
            
                $conn = Cache::get(md5("comet_db_layer"), "file");
                if (empty($conn)) {
                    $conn = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, array(
                        PDO::ATTR_PERSISTENT => true
                    ) );
                    Cache::add(md5("comet_db_layer"), $conn, "file");
                }
                
            }
            
        }

        return $conn;
    }
    
    public static function query($sql = "", $params = array())
    {
        $start = self::_execTime();
        
        if (MEMCACHE_ON) { # Return Memcache results if they exisit
            
            $memcache_id = $this->_createMemcacheId($sql, $params);
            $results     = Cache::get($memcache_id, 'memcache');
            
            if (!empty($results)) {
                return $results;
            }
            
        }
        
        $results = new stdClass();
        $results->success = false;
        $results->results = array();
        
        $conn    = self::init();
        $pdo     = $conn->prepare("{$sql}");
        #$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $success = $pdo->execute($params);
        
        if ($success) {
            
            $results->success = true;
            
            if (preg_match('/SELECT/i', $sql)) {
                $results->results = $pdo->fetchAll(PDO::FETCH_CLASS);
            }
            
            if (preg_match("/INSERT/i", $sql)) {
                $results->last_insert_id = $conn->lastInsertId();
            }
            
        }
        
        if (MEMCACHE_ON) {
            Cache::add($memcache_id, $results, 'memcache');
        }
        
        $end                   = self::_execTime();
        $query_length          = round($end - $start, 4);
        $results->query_length = $query_length;
        $conn                  = null;
        
        return $results;
    }
        
    protected static function _execTime() 
    {
        $a = explode ( ' ', microtime() );
        return( double ) $a[0] + $a[1];
    }
    
    public static function assembleSql($sql, $values)
    {
        foreach( $values as $value ) {            
            $_value = addslashes($value);
            $new_value = "'{$_value}'";  
            $sql = preg_replace( '/\?/', $new_value, $sql, 1 );
        }
        return $sql;
    }
    
    protected static function _createMemcacheId($sql, $values)
    {
        return md5(self::assembleSql($sql, $values));
    }
    
}