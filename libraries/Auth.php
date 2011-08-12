<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth {
	
	private $cookie_name = "autologin";
	private $expiration = 8640000; // 100 days
	
	private $ci;
	
	public $error = FALSE;
	
	public function __construct() {
		$this->ci = &get_instance();
		
		/* load required libraries and models */
		$this->ci->load->library('session');
		$this->ci->load->library('PasswordHash');
		$this->ci->load->model('m_users');
		
		/* get parameters from config if available */
		if($this->ci->config->item('autologin_cookie_name'))
			$this->cookie_name = $this->ci->config->item('autologin_cookie_name');
		if($this->ci->config->item('autologin_expiration'))
			$this->expiration = $this->ci->config->item('autologin_expiration');
		
		/* detect autologin */
		if(!$this->ci->session->userdata('loggedin'))
			$this->autologin();
	}
	
	public function login($username, $password, $remember = FALSE) {
		$user = $this->ci->m_users->get($username, 'username');
		
		if($user) {
			if($user["activated"]) {
				if($this->check_pass($password, $user['password'])) {
					$this->ci->session->set_userdata(array(
						'userid'	=> $user['id'],
						'username'	=> $user['username'],
						'loggedin'	=> TRUE,
					));
					
					if($remember)
						$this->create_autologin($user['id']);
					
					return true;
				}
				else
					$this->error = "wrong_password";
			}
			else
				$this->error = "not_activated";
		}
		else
			$this->error = "not_found";
		
		return false;
	}
	
	private function create_autologin($id) {
		$key = $this->generate_key();
		
		/* clean old keys on this ip */
		$this->ci->load->model('m_autologin');
		$this->ci->m_autologin->purge($id);
		
		if($this->ci->m_autologin->insert($id, $key)) {
			$this->ci->input->set_cookie(array(
				'name' 		=> $this->cookie_name,
				'value'		=> serialize(array('id' => $id, 'key' => $key)),
				'expire'	=> $this->expiration
			));
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	private function delete_autologin() {
		if ($cookie = $this->ci->input->cookie($this->cookie_name, TRUE)) {
			$data = unserialize($cookie);
			
			if (isset($data['id']) AND isset($data['key'])) {
				$this->ci->load->model('m_autologin');
				$this->ci->m_autologin->delete($data['id'], $data['key']);
			}
			
			/* delete cookie */
			$this->ci->input->set_cookie(array(
				'name' 		=> $this->ci->config->item('autologin_cookie_name'),
				'value'		=> "",
				'expire'	=> ""
			));
		}
		
		return TRUE;
	}
	
	private function autologin() {
		if(!$this->loggedin()) {
			if ($cookie = $this->ci->input->cookie($this->cookie_name, TRUE)) {
				$data = unserialize($cookie);
				
				if (isset($data['id']) AND isset($data['key'])) {
					$this->ci->load->model('m_autologin');
					
					if($this->ci->m_autologin->exists($data['id'], $data['key'])) {
						$user = $this->ci->m_users->get($data['id']);
						
						/* logged in */
						$this->ci->session->set_userdata(array(
							'userid'	=> $user['id'],
							'username'	=> $user['username'],
							'loggedin'	=> TRUE,
						));
						
						/* refresh key */
						$new_key = $this->generate_key();
						
						if($this->ci->m_autologin->update($data['id'], $data['key'], $new_key)) {
							$this->ci->input->set_cookie(array(
								'name' 		=> $this->cookie_name,
								'value'		=> serialize(array('id' => $data['id'], 'key' => $new_key)),
								'expire'	=> $this->expiration
							));
						}
						
						return TRUE;
					}
				}
			}
		}
		
		return FALSE;
	}
	
	private function generate_key() {
		return md5(uniqid(rand().$this->ci->config->item('encryption_key')));
	}
	
	public function logout() {
		$this->ci->session->sess_destroy();
		$this->delete_autologin();
		$this->ci->session->set_userdata('loggedin', FALSE);
	}
	
	public function loggedin() {
		return $this->ci->session->userdata('loggedin');
	}
	
	public function userid() {
		return $this->ci->session->userdata('userid');
	}
	
	public function username() {
		return $this->ci->session->userdata('username');
	}
	
	public function hash($password) {
		return $this->ci->passwordhash->HashPassword($password);
	}
	
	private function check_pass($password, $hash) {
		return $this->ci->passwordhash->CheckPassword($password, $hash);
	}
	
}