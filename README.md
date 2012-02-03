Codeigniter secure authentication library
=========================================

This is a secure authentication library for codeigniter.

Installation
------------

Place the files from the repository in their respective folders. A database.sql file is included containing the required database structure.

Configuration
-------------

Edit the auth.php configuration file to fit your specific environment:

    /*
	| -------------------------------------------------------------------
	| Authentication configuration
	| -------------------------------------------------------------------
	| The basic settings for the auth library.
	|
	| 'cookie_name'	   = the name you want for the cookie
	| 'cookie_expire'  = the number of SECONDS you want the cookie to last
	| 'cookie_encrypt' = encrypt cookie with encryption_key
	| 'hash_algorithm' = the hashing algorithm used for autologin keys
	| 'identification' = the database field that is used to identify the user
	*/

	$config['cookie_name']    = 'autologin';
	$config['cookie_expire']  = 31536000;
	$config['cookie_encrypt'] = TRUE;
	$config['hash_algorithm'] = 'sha256';
	$config['identification'] = 'username';

	/*
	| -------------------------------------------------------------------
	| Model options
	| -------------------------------------------------------------------
	| If you use a custom model and or a different database structure, 
	| adjust these values so that the library uses the correct methods.
	|
	| 'primary_key'	= the primary key of your users database table
	| 'user_model'	= the name of your user model
	| 'autologin_model' = the name of the autologin model
	*/

	$config['primary_key'] = 'id';
	$config['user_model']  = 'user_model';
	$config['autologin_model'] = 'autologin_model';

If you prefer, you can autoload the library by adjusting your autoload.php file and add 'auth' to the $autoload['libraries'] array.

The identification field is the user database field you use to identify your users, this will also be the name of the form variable. If you want to identify your users using an email adress, change this field to 'email' (as well as your form field).
	
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

    $this->auth->username() # or how your column is called
returns the current user's username

    $this->auth->email() # or how your column is called
returns the current user's email

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
		$id  = $this->auth->userid():
		$username = $this->auth->username();
	
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