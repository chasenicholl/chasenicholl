<?php
class Utility
{

    public static function validateFields($input = array(), $required = array())
    {
        $respose = new stdClass();
        $failed  = array();
        $passed  = array();
        $checks  = count($required);
        
        foreach ($required as $field) {
            if (array_key_exists($field, $input)) {
                $value = (string)$input[$field];
                if (strlen($value) > 0) {
                    $passed[] = true;
                }
                else {
                    $failed[] = $field;    
                }
            }
            else {
                $failed[] = $field;
            }
        }
        
        if (count($passed) === $checks) {
            $respose->success = true;
        }
        else {
            $respose->success = false;
            $respose->error_code = "missing_required_fields";
            $respose->error      = "Missing Required Fields.";
            $respose->errors     = $failed;
        }
        
        return $respose;
    }
    
    public static function currency($float)
    {
        return "$". number_format( round($float, 2), 2);
    }
    
}