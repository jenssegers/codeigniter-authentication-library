<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin extends CI_Controller {
    
    public function index() {
        // in case you did not autoload the library
        $this->load->library('auth');
        
        if (!$this->auth->loggedin()) {
            redirect('login');
        }
        
        // get current user id
        $id = $this->auth->userid();
        
        // get user from database
        $this->load->model('user_model');
        $user = $this->user_model->get('id', $user);
        
        echo 'Welcome to the super secret section, ' . $user['username'];
    }

}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */