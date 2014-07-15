<?php
class Model
{
    
    var $table_name   = null;
    var $is_loaded    = false;
    var $data_changed = false;
    var $saved        = false;
    
    var $orig_data  = null;
    var $data       = null;

    public function __call($name, $values)
    {
    
        switch (strtolower(substr($name, 0, 3))) {
            
            case 'get':
            
                $name = preg_replace('/get/', '', $name);
                $key = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
                return $this->getData($key);                
                break;
            
            case 'set':
                
                $name = preg_replace('/set/', '', $name);
                $key = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
                
                if (isset($values[0])) {
                    $this->setData($key, $values[0]);
                }
                
                break;
            
        }
        
        return $this;
        
    }

    public static function get($class_name = "", $get_cached = true)
    {
        $model_name = Comet::camalizeClassName($class_name);
        if (class_exists($model_name)) {
            $model = new $model_name();
            $model->table_name = Comet::getTableNameFromClass($model_name);
            return $model;
        }
        else {
            $empty_model = new Model();
            $empty_model->table_name = Comet::getTableNameFromClass($model_name);
            return $empty_model;
        }
    }
    
    public function load($cols = null, $limit = 1)
    {

        if (!is_array($cols) && is_numeric($cols)) { # Are we looking by index?
            $cache_id = $this->table_name . $cols;
        }
        elseif (is_array($cols)) { # Or are we building a query?
            $cache_id = $this->table_name . implode("", $cols);
        }
        
        # Is the Model cached and does NOT have changes? Return cached
        $cached_this = Cache::get($cache_id, 'file');
        if (!empty($cached_this)) {
            
            if (!$cached_this->data_changed) {
                return $cached_this;
            }
            
        }
        
        # Check limit
        if (!$limit) {
            $_limit = "";
        }
        else {
            $_limit = "LIMIT {$limit}";
        }
    
        # Query to load Model
        if (!is_array($cols) && is_numeric($cols)) {
            $res = Db::query("SELECT * FROM {$this->table_name} WHERE id = ? {$_limit}", array($cols));
        }
        elseif (is_array($cols)) {
            
            $prepares = "";
            $values   = array();
            
            foreach ($cols as $col => $val) {
                $prepares .= "{$col} = ? AND ";
                $values[] = $val;
            }
            $prepares = substr($prepares, 0, -5);
            $res = Db::query("SELECT * FROM {$this->table_name} WHERE {$prepares} {$_limit}", $values);

        }
        
        if ($res->success) {
            
            if (isset($res->results[0])) {
                                
                $this->data_changed = false; # Reset Flag
                $this->is_loaded    = true;
                
                foreach ($res->results as $results) {
                    $this->orig_data[] = $results;
                }
                
                Cache::add($cache_id, $this, 'file');

            }
            
        }

        return $this;
    }
    
    public function isLoaded()
    {
        if ($this->is_loaded) {
            return true;
        }
        return false;
    }
    
    public function setData($name, $value)
    {
        if (empty($this->data)) {
            $this->data = array();
        }
        else {
            $this->data = (array)$this->data;    
        }
        
        $key = count($this->data) - 1;
        if ($key < 0) $key = 0;
        
        if (!isset($this->data[$key])) {
            $this->data[$key] = array();
        }
        
        $this->data[$key]        = (array)$this->data[$key];
        $this->data[$key][$name] = $value;
        $this->data[$key]        = (object)$this->data[$key];
        
        if ($this->hasDataChanged()) {
            $this->data_changed = true;
        }
        
        return $this;
    }

    public function getData($key = null, $is_array = false)
    {
        if (!empty($key)) {
            
            $all_data = (array)$this->getData();
            if (isset($all_data[$key])) {
                return $all_data[$key];
            }

        }
        else {
            
            if (!$is_array) {
                
                $all_data = array();
                
                if (empty($this->orig_data)) {
                    $this->orig_data = $this->data;
                }
                
                foreach ($this->orig_data as $k => $data) {
                    
                    if (isset($this->data[$k])) {
                        $all_data[$k] = array_merge((array)$data, (array)$this->data[$k]);
                    }
                    else {
                        $all_data[$k] = (array)$data;
                    }
                    
                }
                
                if (count($all_data) == 1 && isset($all_data[0])) {
                    return (object)$all_data[0];
                }
                else {
                    $_all_data = array();
                    foreach ($all_data as $ad) {
                        $_all_data = (object)$ad;    
                    }
                    return (object)$_all_data;
                }
                
            }
            else {
                
                $all_data = array();
                foreach ($this->orig_data as $k => $data) {
                    
                    if (isset($this->data[$k])) {
                        $all_data[$k] = array_merge((array)$data, (array)$this->data[$k]);
                    }
                    
                }
                
                if (count($all_data) == 1 && isset($all_data[0])) {
                    return $all_data[0];
                }
                else {
                    return $all_data;
                }
                
            }
        
        }
    }
    
    public function getOrigData()
    {
        $og_data = (array)$this->orig_data;
        if (count($og_data) == 1 && isset($og_data[0])) {
            return (object)$og_data[0];
        }
        else {
            $_all_data = array();
            foreach ($og_data as $od) {
                $_all_data = (object)$od;    
            }
            return (object)$_all_data;   
        }
    }
    
    public function hasDataChanged()
    {
        $changes = (array)$this->getDataDiff();
        if (!empty($changes)) {
            return true;
        }
        
        return false;
    }
    
    public function getDataDiff()
    {
        $changes   = array();
        $data      = $this->getData();
        $orig_data = $this->getOrigData();
        
        # We need to check session cache too i think
        #$cache_id    = $this->table_name . $this->orig_data->id;
        #$cached_data = Cache::get($cache_id, 'file');
        ####
        
        $_data      = (array)$data;
        $_orig_data = (array)$orig_data;
        
        if (isset($_data[0])) {
            
            foreach ($_data as $k => $d) {
                
                if (isset($_orig_data[$k])) {
                    
                    foreach ($_orig_data[$k] as $kk => $vv) {
                        
                        if (isset($_data[$kk])) {
                            if ($_data[$kk] != $vv) {
                                $changes[$k][$kk] = $_data[$kk];
                            }
                        }
                        
                    }
                    
                    //$changes[] = array_diff($d, $_orig_data[$k]);
                }
                
            }
                        
        }
        else {
            
            foreach ($_orig_data as $k => $v) {
                
                if (isset($_data[$k])) {
                    if ($_data[$k] != $v) {
                        $changes[$k] = $_data[$k];
                    }
                } 
                
            }
            
        }
        
        return (object)$changes;
    }
    
    public function save()
    {
    
        if ($this->isLoaded() && $this->hasDataChanged()) {
                                                   
            $new_data = $this->getDataDiff();
            $cols     = "";
            $prepares = "";
            $values   = array();
            
            foreach ($new_data as $col => $val) {
                $cols     .= $col . " = ?, ";
                $values[] = $val;
            }
            $cols = substr($cols, 0, -2);
            
            $res = Db::query("UPDATE {$this->table_name} SET {$cols} WHERE id = ? LIMIT 1", array_merge(
                $values,
                array($this->getOrigData()->id)
            ) );
            
            if ($res->success) {
                
                $this->orig_data    = $this->getData();
                $this->data         = null;
                $this->data_changed = true;
                $this->saved        = true;
                
                Cache::add($this->table_name . $this->orig_data->id, $this, 'file');
                return true;
                
            }
                        
        }
        else {
            
            $values   = array();
            $prepares = "";
            $cols     = "";
            
            $data = $this->getData();
            
            foreach ($data as $key => $val) {
                $cols     .=  "{$key}, ";
                $prepares .= "?, ";
                $values[] = $val;
            }
            
            $cols     = substr($cols, 0, -2);
            $prepares = substr($prepares, 0, -2);
            $res = Db::query("INSERT INTO {$this->table_name} ({$cols}) VALUES ({$prepares})", $values);
            if ($res->success) {
                return $res->last_insert_id;
            }
            
        }

    }
    
    public function delete()
    {
        $sql = "DELETE FROM {$this->table_name} WHERE id = ? LIMIT 1";
        $res = Db::query($sql, array($this->getId()));
        if ($res->success) {
            return true;
        }
        return false;
    }
    
    public function getGrid($page = 1, $sort = "updated_at", $direction = "DESC", $valid_sorts = array())
    {
        $page_size = 10;
        $totals    = $this->getTotalsCount();
        
        $pages = 0;
        $count = 0;
        for ($i = 0; $i < $totals; $i++) {
            $count++;
            if ($count == $page_size) {
                $pages++;
                $count = 0;
            }
        }
        
        $start = (($page - 1) * 10);        
        $grid = array(
            $this->table_name => null,
            'total'           => $totals,
            'pages'           => ((!$pages) ? 1 : $pages)
        );
        
        if (empty($valid_sorts)) {
            $valid_sorts = array('updated_at');
        }
        
        $valid_direction = array(
            'desc',
            'asc'
        );
    
        if (!in_array($sort, $valid_sorts)) {
            $sort = 'updated_at';
        }
                 
        if (!in_array($direction, $valid_direction)) {
            $direction = 'desc';
        }
        
        error_log("SELECT * FROM {$this->table_name} ORDER BY {$sort} {$direction} LIMIT {$start}, {$page_size}");
        $res = Db::query("SELECT * FROM {$this->table_name} ORDER BY {$sort} {$direction} LIMIT {$start}, {$page_size}");
        
        if ($res->success) {
            foreach ($res->results as $k => $results) {
                $grid[$this->table_name][$k] = $results;   
                $grid[$this->table_name][$k]->page = $page;
            }
        }

        return $grid;
    }
    
    public function getTotalsCount()
    {
        $res = Db::query("SELECT COUNT(id) AS count FROM {$this->table_name}");
        if ($res->success && isset($res->results[0])) {
            return $res->results[0]->count;
        }
        return 0;
    }
    
}