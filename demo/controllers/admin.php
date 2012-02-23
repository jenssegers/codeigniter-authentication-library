<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin extends CI_Controller {
    
    public function index() {
        // in case you did not autoload the library
        $this->load->library("auth");
        
        if (!$this->auth->loggedin())
            redirect("login");
        
        echo "Welcome to the super secret section, " . $this->auth->user->username;
    }

}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */