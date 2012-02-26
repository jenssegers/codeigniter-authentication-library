<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Login extends CI_Controller {
    
    public function index() {
        // in case you did not autoload the library
        $this->load->library('auth');
        
        // user is already logged in
        if ($this->auth->loggedin()) {
            redirect('admin');
        }
        
        $error = '';
        
        // form submitted
        if ($this->input->post('username') && $this->input->post('password')) {
            $remember = $this->input->post('remember') ? TRUE : FALSE;
            
            // get user from database
            $this->load->model('user_model');
            $user = $this->user_model->get('username', $this->input->post('username'));
            
            if ($user) {
                // compare passwords
                if ($this->user_model->check_password($this->input->post('password'), $user['password'])) {
                    // mark user as logged in
                    $this->auth->login($user['id'], $remember);
                    redirect('admin');
                } else {
                    $error = 'Wrong password';
                }
            } else {
                $error = 'User does not exist';
            }
        }
        
        // show login form
        $this->load->helper('form');
        $this->load->view('login', array('error' => $error));
    }
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */