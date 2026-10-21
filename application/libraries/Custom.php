<?php
/**
 * NOTE: THIS IS A CUSTOM CLASS LIBRARY FOR ALL REUSABLE FUNCTIONS IN HRMS
 * DON'T FORGET TO MAKE EACH FUNCTIONS YOU CREATE A DYNAMIC ONE.
*/

defined('BASEPATH') OR exit('No direct script access allowed');

Class Custom 
{    
    public function some_method()
    {
        return "This is a sample library method";
    }

    function random()
    {
        $length = 5;
        $list = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        mt_srand((float)microtime() * 1000000);
        $newstring = "";

        if ($length > 0) {
            while (strlen($newstring) < $length) {
                $newstring .= $list[mt_rand(0, strlen($list) - 1)];
            }
            }       
        return $newstring;
    }

    function getAge( $dob, $tdate )
    {
        $age = 0;
        while( $tdate >= $dob = strtotime('+1 year', $dob)){
                ++$age;
        }return $age;
    }

    
    


}
?>