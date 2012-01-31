<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	public function index()
	{
		/* if you did not autoload the library */
		$this->load->library("auth");
		
		$this->load->helper("form");
		
		if($this->auth->loggedin()) {
			/* user is still logged in */
			redirect("admin");
		}
		else {
			/* form submitted */
			if($this->input->post("username") && $this->input->post("password")) {
				$remember = $this->input->post("remember")?TRUE:FALSE;
				
				if($this->auth->login($this->input->post("username"), $this->input->post("password"), $remember)) {
					/* credentials are correct */
					redirect("admin");
				}
				else {
					/* login failed, show form with errors */
					$error = $this->auth->error;
					
					switch($error) {
						case "not_found":
							$error = "Account not found";
							break;
						case "not_activated":
							$error = "Account not activated";
							break;
						case "wrong_password":
							$error = "Wrong password";
							break;
					}
					
					$this->load->view("login", array("error"=>$error));
				}
			}
			else {
				$this->load->view("login");
			}
		}
	}
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */