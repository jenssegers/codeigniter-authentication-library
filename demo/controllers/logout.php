<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Logout extends CI_Controller {
    
    public function index() {
        // in case you did not autoload the library
        $this->load->library('auth');
        
        $this->auth->logout();
        redirect('login');
    }

}

/* End of file logout.php */
/* Location: ./application/controllers/logout.php */