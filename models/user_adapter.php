<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_adapter extends Model_adapter {
    
    // The name of the original model, EDIT THIS!
    protected $model = "user_model";
    
    /**
     * THIS IS A WRAPPER METHOD FOR AN EXISTING METHOD IN YOUR ORIGINAL MODEL
     * Return the complete user array matching these parameters
     * 
     * @param string where
     * @param int value
     * @return array $user
     */
    public function get($where, $value) {
        /* EDIT THIS: */
        return parent::get_where($where, $value);
    }

}

/**
 * A model adapter class that will pass all calls made to the original model
 * DO NOT EDIT THIS CLASS!
 */
class Model_adapter extends CI_Model {
    
    protected $model = 'user_model';
    
    function __construct() {
        parent::__construct();
        if (!class_exists($this->model)) {
            $ci = &get_instance();
            $ci->load->model($this->model);
        }
    }
    
    function __get($name) {
        $ci = &get_instance();
        return $ci->{$this->model}->{$name};
    }
    
    function __call($method, $args) {
        $ci = &get_instance();
        return call_user_func_array(array($ci->{$this->model}, $method), $args);
    }

}