<?php
/**
 * @name		CodeIgniter Secure Authentication Library
 * @author		Jens Segers
 * @link		http://www.jenssegers.be
 * @license		MIT License Copyright (c) 2011 Jens Segers
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

class Auth {
    
    /* basic settings default values */
    private $cookie_name    = "autologin";
    private $cookie_expire  = 8640000;
    private $cookie_encrypt = TRUE;
    private $hash_algorithm = "sha256";
    private $identification = "username";
    
    /* model options default values */
    private $primary_key = "id";
    private $user_model  = "user_model";
    private $autologin_model = "autologin_model";
    
    private $ci;
    public $error = FALSE;
    
    public function __construct($config = array()) {
        $this->ci = &get_instance();
        
        /* load required libraries and models */
        $this->ci->load->library('session');
        $this->ci->load->library('PasswordHash', array("iteration_count_log2" => 8, "portable_hashes" => FALSE));
        
        /* HVMC support */
        $this->ci->load->model($this->user_model);
        if (strstr($this->user_model, '/')) {
            $this->user_model = end(explode('/', $this->user_model));
        }
        
        /* initialize from config */
        if (!empty($config)) {
            $this->initialize($config);
        }
        
        log_message('debug', 'Authentication library initialized');
        
        /* detect autologin */
        if (!$this->ci->session->userdata('loggedin')) {
            $this->autologin();
        }
    }
    
    /**
     * Initialize with configuration array
     * @param array $config
     */
    public function initialize($config = array()) {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
    }
    
    /**
     * Easy access to the current user's information
     * This enables functions like username() and email()
     * @param string $name
     * @param array $arguments
     * @return unknown
     */
    public function __call($name, $arguments) {
        if ($this->loggedin()) {
            $user = $this->ci->session->userdata('user');
            if (isset($user[$name])) {
                return $user[$name];
            }
        }
        return FALSE;
    }
    
    /**
     * Authenticate a user using their credentials and choose whether or not to create an autologin cookie
     * Returns TRUE if login is successful, false otherwise
     * @param string $identification
     * @param string $password
     * @param boolean $remember
     * @return boolean
     */
    public function login($identification, $password, $remember = FALSE) {
        $user = $this->ci->{$this->user_model}->get($identification, $this->identification);
        
        if ($user) {
            if ($user["activated"]) {
                if ($this->check_pass($password, $user['password'])) {
                    /* remove password and store user information in session */
                    unset($user["password"]);
                    $this->ci->session->set_userdata(array('user' => $user, 'loggedin' => TRUE));
                    
                    if ($remember) {
                        $this->create_autologin($user[$this->primary_key]);
                    }
                    
                    return TRUE;
                } else {
                    $this->error = "wrong_password";
                }
            } else {
                $this->error = "not_activated";
            }
        } else {
            $this->error = "not_found";
        }
        
        return FALSE;
    }
    
    /**
     * Logout the current user, destroys the current session and autologin key
     */
    public function logout() {
        $this->ci->session->sess_destroy();
        $this->delete_autologin();
        $this->ci->session->set_userdata('loggedin', FALSE);
        $this->ci->session->set_userdata('user', FALSE);
    }
    
    /**
     * Check if the current user is logged in or not
     * @return boolean
     */
    public function loggedin() {
        return $this->ci->session->userdata('loggedin');
    }
    
    /**
     * Returns the user id of the current user if logged in
     * @return int
     */
    public function userid() {
        $user = $this->ci->session->userdata('user');
        return $user[$this->primary_key];
    }
    
    /**
     * Returns the identification field of the current user if logged in
     * @return int
     */
    public function identification() {
        $user = $this->ci->session->userdata('user');
        return $user[$this->identification];
    }
    
    /**
     * Creates the hash for a given password, use this method in your user model
     * @param string $password
     */
    public function hash($password) {
        return $this->ci->passwordhash->HashPassword($password);
    }
    
    /**
     * Contains an error message when the login has failed
     * @return string
     */
    public function error() {
        return $this->error;
    }
    
    /**
     * Generate a new autologin token and create the autologin cookie, given a user's id
     * @param int $id
     * @return boolean
     */
    private function create_autologin($id) {
        $key = $this->generate_key();
        
        /* HVMC support */
        $this->ci->load->model($this->autologin_model);
        $autologin_model = strstr($this->autologin_model, "/") ? end(explode("/", $this->autologin_model)) : $this->autologin_model;
        
        /* clean old keys on this ip */
        $this->ci->{$autologin_model}->purge($id);
        
        if ($this->ci->{$autologin_model}->insert($id, hash($this->hash_algorithm, $key))) {
            $data = serialize(array('id' => $id, 'key' => $key));
            
            /* encrypt cookie */
            if ($this->cookie_encrypt) {
                $this->ci->load->library('encrypt');
                $data = $this->ci->encrypt->encode($data);
            }
            
            $this->ci->input->set_cookie(array('name' => $this->cookie_name, 'value' => $data, 'expire' => $this->cookie_expire));
            
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Disable the current autologin token and remove the cookie
     */
    private function delete_autologin() {
        if ($cookie = $this->ci->input->cookie($this->cookie_name, TRUE)) {
            /* decrypt cookie */
            if ($this->cookie_encrypt) {
                $this->ci->load->library('encrypt');
                $data = $this->ci->encrypt->decode($cookie);
            }
            
            $data = @unserialize($data);
            
            if (isset($data['id']) and isset($data['key'])) {
                /* HVMC support */
                $this->ci->load->model($this->autologin_model);
                $autologin_model = strstr($this->autologin_model, "/") ? end(explode("/", $this->autologin_model)) : $this->autologin_model;
                
                /* delete the key */
                $this->ci->{$autologin_model}->delete($data['id'], hash($this->hash_algorithm, $data['key']));
            }
            
            /* delete cookie */
            $this->ci->input->set_cookie(array('name' => $this->ci->config->item('autologin_cookie_name'), 'value' => "", 'expire' => ""));
        }
    }
    
    /**
     * Detects the autologin cookie and logs in the user if the token is valid
     * @return boolean
     */
    private function autologin() {
        if (!$this->loggedin()) {
            if ($cookie = $this->ci->input->cookie($this->cookie_name, TRUE)) {
                /* decrypt cookie */
                if ($this->cookie_encrypt) {
                    $this->ci->load->library('encrypt');
                    $data = $this->ci->encrypt->decode($cookie);
                }
                
                $data = @unserialize($data);
                
                if (isset($data['id']) and isset($data['key'])) {
                    /* HVMC support */
                    $this->ci->load->model($this->autologin_model);
                    $autologin_model = strstr($this->autologin_model, "/") ? end(explode("/", $this->autologin_model)) : $this->autologin_model;
                    
                    /* check for valid key */
                    if ($this->ci->{$autologin_model}->exists($data['id'], hash($this->hash_algorithm, $data['key']))) {
                        $user = $this->ci->{$this->user_model}->get($data['id']);
                        
                        /* remove password and store user information in session */
                        unset($user["password"]);
                        $this->ci->session->set_userdata(array('user' => $user, 'loggedin' => TRUE));
                        
                        /* refresh key */
                        $new_key = $this->generate_key();
                        
                        if ($this->ci->{$autologin_model}->update($data['id'], hash($this->hash_algorithm, $data['key']), hash($this->hash_algorithm, $new_key))) {
                            $data = serialize(array('id' => $data['id'], 'key' => $new_key));
                            
                            /* encrypt cookie */
                            if ($this->cookie_encrypt) {
                                $this->ci->load->library('encrypt');
                                $data = $this->ci->encrypt->encode($data);
                            }
                            
                            $this->ci->input->set_cookie(array('name' => $this->cookie_name, 'value' => $data, 'expire' => $this->cookie_expire));
                        }
                        
                        return TRUE;
                    }
                }
            }
        }
        
        return FALSE;
    }
    
    /**
     * Generate random autologin tokens
     * @return string
     */
    private function generate_key() {
        return hash($this->hash_algorithm, uniqid(rand() . $this->ci->config->item('encryption_key')));
    }
    
    /**
     * Checks the given password with the correct hash (using phpass)
     * @param string $password
     * @param string $hash
     * @return boolean
     */
    private function check_pass($password, $hash) {
        return $this->ci->passwordhash->CheckPassword($password, $hash);
    }

}