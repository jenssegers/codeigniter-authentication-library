<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Let CodeIgniter load the parent class
$ci = &get_instance();
$ci->load->model("user_model"); // EDIT THIS!

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