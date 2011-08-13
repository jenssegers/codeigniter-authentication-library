Codeigniter secure authentication library
=========================================

This is a secure authentication library for codeigniter.

Installation
------------

Place the files from the repository in their respective folders. A database.sql file is included containing the required database structure.


Configuration
-------------

In your config.php add the following configuration parameters (optional):

    /*
    |--------------------------------------------------------------------------
    | Autologin
    |--------------------------------------------------------------------------
    |
    | 'autologin_cookie_name' = the name you want for the cookie
    | 'autologin_expiration'  = the number of SECONDS you want the session to last.
    |
    */
    $config['autologin_cookie_name'] = "autologin";
    $config['autologin_expiration']  = 31536000; // 1 year

If you prefer, you can autoload the library by adjusting your autoload.php file and add 'auth' to the $autoload['libraries'] array.

This library will detect if you have enabled $config['sess_encrypt_cookie'] and will encrypt the autologin cookie if so. This obscures the cookie for extra protection.
	
Usage
-----

A simple implementation example of this library is included, so be sure to check out the example. These are the available methods:

    $this->auth->login($username, $password, $remember)
authenticate a user using their credentials and choose whether or not to create an autologin cookie
	
    $this->auth->logout()
logout function, destroys session and autologin keys

    $this->auth->loggedin()
returns whether the user is logged in or not, TRUE/FALSE

    $this->auth->userid()
returns the current user's id

    $this->auth->username()
returns the current user's username

    $this->auth->hash($password)
returns the hashed password to store in the database (to use in your model)

Controller example
------------------

	if($this->auth->loggedin()) {
		/* user is already logged in */
		redirect("admin");
	}
		 
	if($this->auth->login($this->input->post("username"), $this->input->post("password"), TRUE)) {
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
			default:
				$error = "Login error";
		}
		 
		$this->load->view("login", array("error"=>$error));
	}