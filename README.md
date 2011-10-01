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
    | 'autologin_expiration'  = the number of SECONDS you want the session to last
    | 'autologin_encrypt'     = encrypt cookie with encryption_key
    | 'autologin_hash_algo'   = the hashing algorithm used for autologin keys
	| 'autologin_identification = the user field that is used to identify the user
    |
    */
    $config['autologin_cookie_name'] = "autologin";
    $config['autologin_expiration']  = 31536000; // 1 year
    $config['autologin_encrypt']     = TRUE;
    $config['autologin_hash_algo']   = "sha256";
	$config['autologin_identification'] = "username";

If you prefer, you can autoload the library by adjusting your autoload.php file and add 'auth' to the $autoload['libraries'] array.

The identification field is the user database field you use to identify your users, this will also be the name of the form variable. If you want to identify your users using an email adress, change this field to 'email' (as well as your form field).

This library will detect if you have enabled sess_encrypt_cookie and will encrypt the autologin cookie if you did not specify autologin_encrypt. Encrypting obscures the cookie for extra protection.
	
Usage
-----

A simple implementation example of this library is included, so be sure to check out the example. These are the available methods:

    $this->auth->login($identification, $password, $remember)
authenticate a user using their credentials and choose whether or not to create an autologin cookie
	
    $this->auth->logout()
logout function, destroys session and autologin keys

    $this->auth->loggedin()
returns whether the user is logged in or not, TRUE/FALSE

    $this->auth->userid()
returns the current user's id

    $this->auth->identification()
returns the current user's identification field

    $this->auth->hash($password)
returns the hashed password to store in the database (to use in your model)

If the login would fail, an error message is stored in $this->auth->error.

Model communication
-------------------

This library does not serve as a model for your user database. This library is developed in such a way that it can be coupled to whatever user model you are using. Many other authentication act as a model (or a facade). This is not what I wanted for this library, because libraries should be exchangeable between projects.

The library only uses the get($id) method of the included example model to retrieve a specific user's information. Feel free to change the model name and methods to adjust the library to your environment.

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