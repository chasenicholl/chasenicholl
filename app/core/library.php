<?php
class Library
{
    
    public static function get($lib)
    {
        $lib_file = get_include_path() . "/lib/" . $lib . ".php";
        if (is_file($lib_file)) {
            include_once($lib_file);
            $library_name = Comet::camalizeClassName($lib);
            if (class_exists($library_name)) {
                return new $library_name();
            }
        }

    }
    
}