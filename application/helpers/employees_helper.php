<?php

class EmployeesHelper {

    public static function getName($id)
    {
        $CI = &get_instance(); // Get the CI_Controller instance
        $CI->load->model('Utilities_model', 'utimodel'); // Load the model if not loaded already
      
        return @$CI->utimodel->get_field("name", "employee3", "emp_id = '$id'")->name;  
    }
    
}
?>