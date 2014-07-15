<?php
class Cache
{
    
    public static $memcache = null;
    
    public static function add($id = null, $data = null, $type = 'file')
    {
        global $COMMET_CACHE; # Hold request for request
        
        if (!empty($id)) {
            
            $id = md5($id);
            
            if (empty($data)) {
                $data = null; # Make sure datas clean if empty
            }

            if ($type == 'file') {
               
                if (!isset($COMMET_CACHE[$id])) {
                    $COMMET_CACHE[$id] = array();
                }
                $COMMET_CACHE[$id] = $data; # Add to request cache
            
            }
            elseif ($type == 'session') {
             
                if (!isset($_SESSION['COMET_CACHE'][$id])) {
                    $_SESSION['COMET_CACHE'][$id] = array();
                }
                $_SESSION['COMET_CACHE'][$id] = $data; # Add to session persistant cache
                
            }
            elseif ($type == 'memcache') {
                
                self::_addToMemcache($id, $data);
                
            }
            elseif ($this->type == 'all') {
                
                self::add($id, $data, 'file');
                self::add($id, $data, 'session');
                self::add($id, $data, 'memcache');
                
            }
            
            return true;
            
        }
        
        return false;
    }
    
    public static function get($id, $type = '')
    {
        
        global $COMMET_CACHE; # Hold request for request
        
        if (!empty($id)) {
            
            $id = md5($id);
            
            if ($type == 'file') {

                if (isset($COMMET_CACHE[$id])) {
                    return $COMMET_CACHE[$id];
                }
                
                
            }
            elseif ($type == 'session') {
            
                if (isset($_SESSION['COMET_CACHE'][$id])) {
                    return $_SESSION['COMET_CACHE'][$id];
                }
                
            }
            elseif ($type == 'memcache') {
            
                return self::_getFromMemcache($id);
                
            }

        }
        
        return false;
    }
    
    public static function clearAll($type = '')
    {
        
        global $COMMET_CACHE; # Hold request for request
        
        if ($type == 'file') {
            $COMMET_CACHE = array();
        }
        elseif ($type == 'session') {
            $_SESSION['COMET_CACHE'] = array();
        }
        elseif ($type == 'memcache') {
            self::_pergeMemcache();
        }
        elseif ($this->type == 'all') {
            self::clearAll('file');
            self::clearAll('session');
            self::clearAll('memcache');
        }
    }
    
    public static function clear($id, $type)
    {
        
        global $COMMET_CACHE; # Hold request for request
        
        if (!empty($id)) {
                
            $id = md5($id);
            
            if ($type == 'file') {
                
                if (isset($COMMET_CACHE[$id])) {
                    unset($COMMET_CACHE[$id]);
                }
                
            }
            elseif ($type == 'session') {
                
                if (isset($_SESSION['COMET_CACHE'][$id])) {
                    unset($_SESSION['COMET_CACHE'][$id]);
                }
            
            }
            elseif ($type == 'memcache') {
                
                self::_deleteFromMemcache($id);
                
            }
            
        }
    }
    
    protected static function _initMemcache()
    {
        if (empty(self::$memcache)) {
            self::$memcache = new Memcache();
            self::$memcache->pconnect(MEMCACHE_HOST, MEMCACHE_PORT);
        }
        return self::$memcache;
    }
    
    protected static function _addToMemcache($id, $data)
    {
        self::_initMemcache();
        self::$memcache->set($id, $data, 0, MEMCACHE_EXPIRE);
    }
    
    protected static function _getFromMemecache($id)
    {
        if (!$id) {
            # need to throw error
            return false;
        }
        
        self::_initMemcache();
        $results = self::$memcache->get($id);
        
        if (!empty($results)) {
            return $results;
        }
        
        return null;
    }
    
    protected static function _deleteFromMemcache($id)
    {
        if (!$id) {
            # need to throw error
            return false;
        }
        
        $self::_initMemcache();
        self::$memcache->delete($id);
    }
    
    protected static function _pergeMemcache()
    {
        self::_initMemcache();
        self::$memcache->flush();
    }
    
}