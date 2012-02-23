<?php
defined('BASEPATH') or exit('No direct script access allowed');

// The name of your original model, EDIT THIS!
$model = "user_model";

// Load the original model if not loaded yet
if(!class_exists($model)) {
    $ci = &get_instance();
    $ci->load->model($model);
}

/**
 * Make sure this adapter extends your own user model!
 */
class User_adapter extends User_model {
    
    /**
     * Return the complete user array with this id
     * This method is a wrapper an existing method in your own model,
     * adjust this method to use it correctly!
     * 
	 * @param string where
     * @param int value
     * @return array $user
     */
    public function get($where, $value) {
        /* EDIT THIS! */
        $user = parent::get($where, $value);
        
        return $user;
    }

}