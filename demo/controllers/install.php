<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Install extends CI_Controller {
    
    /**
     * This is a demo controller that allows you to add your first user account
     * to the database, please remove this controller afterwards.
     */
    public function index() {
        // load the model
        $this->load->model('user_model');
        
        /* EDIT THESE FIELDS */
        $user = array();
        $user['username'] = 'admin';
        $user['password'] = 'pass';
        $user['email'] = 'my@mail.com';
        
        $id = $this->user_model->insert($user);
    }
}

/* End of file install.php */
/* Location: ./application/controllers/install.php */