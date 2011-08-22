<?php
/**
* @name			CodeIgniter Secure Authentication Library
* @author		Jens Segers
* @link			http://www.jenssegers.be
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

defined('BASEPATH') or exit('No direct script access allowed');

class Auth {
	
	private $cookie_name = "autologin";
	private $expiration = 8640000; // 100 days
	private $encrypt_cookie = TRUE;
	private $hash_algo = "sha256"; // for autologin token
	
	private $ci;
	
	public $error = FALSE;
	
	public function __construct() {
		$this->ci = &get_instance();
		
		/* load required libraries and models */
		$this->ci->load->library('session');
		$this->ci->load->library('PasswordHash', array("iteration_count_log2"=>8, "portable_hashes"=>FALSE));
		$this->ci->load->model('m_users');
		
		/* get parameters from config if available */
		if($this->ci->config->item('autologin_cookie_name'))
			$this->cookie_name = $this->ci->config->item('autologin_cookie_name');
		if($this->ci->config->item('autologin_expiration'))
			$this->expiration = $this->ci->config->item('autologin_expiration');
		if($this->ci->config->item('autologin_encrypt'))
			$this->encrypt_cookie = $this->ci->config->item('autologin_encrypt');
		elseif($this->ci->config->item('sess_encrypt_cookie'))
			$this->encrypt_cookie = $this->ci->config->item('sess_encrypt_cookie');
		if($this->ci->config->item('autologin_hash_algo'))
			$this->hash_algo = $this->ci->config->item('autologin_hash_algo');
		
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
	
	public function error() {
		return $this->error;
	}
	
	private function create_autologin($id) {
		$key = $this->generate_key();
		
		/* clean old keys on this ip */
		$this->ci->load->model('m_autologin');
		$this->ci->m_autologin->purge($id);
		
		if($this->ci->m_autologin->insert($id, hash($this->hash_algo, $key))) {
			$data = serialize(array('id' => $id, 'key' => $key));
			
			/* encrypt cookie */
			if($this->encrypt_cookie) {
				$this->ci->load->library('encrypt');
				$data = $this->ci->encrypt->encode($data);
			}
			
			$this->ci->input->set_cookie(array(
				'name' 		=> $this->cookie_name,
				'value'		=> $data,
				'expire'	=> $this->expiration
			));
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	private function delete_autologin() {
		if ($cookie = $this->ci->input->cookie($this->cookie_name, TRUE)) {
			/* decrypt cookie */
			if($this->encrypt_cookie) {
				$this->ci->load->library('encrypt');
				$data = $this->ci->encrypt->decode($cookie);
			}
			
			$data = unserialize($data);
			
			if (isset($data['id']) AND isset($data['key'])) {
				$this->ci->load->model('m_autologin');
				$this->ci->m_autologin->delete($data['id'], hash($this->hash_algo, $data['key']));
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
				/* decrypt cookie */
				if($this->encrypt_cookie) {
					$this->ci->load->library('encrypt');
					$data = $this->ci->encrypt->decode($cookie);
				}
				
				$data = unserialize($data);
				
				if (isset($data['id']) AND isset($data['key'])) {
					$this->ci->load->model('m_autologin');
					
					if($this->ci->m_autologin->exists($data['id'], hash($this->hash_algo, $data['key']))) {
						$user = $this->ci->m_users->get($data['id']);
						
						/* logged in */
						$this->ci->session->set_userdata(array(
							'userid'	=> $user['id'],
							'username'	=> $user['username'],
							'loggedin'	=> TRUE,
						));
						
						/* refresh key */
						$new_key = $this->generate_key();
						
						if($this->ci->m_autologin->update($data['id'], hash($this->hash_algo, $data['key']), hash($this->hash_algo, $new_key))) {
							$data = serialize(array('id' => $data['id'], 'key' => $new_key));
							
							/* encrypt cookie */
							if($this->encrypt_cookie) {
								$this->ci->load->library('encrypt');
								$data = $this->ci->encrypt->encode($data);
							}
							
							$this->ci->input->set_cookie(array(
								'name' 		=> $this->cookie_name,
								'value'		=> $data,
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
		return hash($this->hash_algo, uniqid(rand().$this->ci->config->item('encryption_key')));
	}
	
	private function check_pass($password, $hash) {
		return $this->ci->passwordhash->CheckPassword($password, $hash);
	}
	
}