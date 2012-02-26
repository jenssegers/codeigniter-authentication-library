CodeIgniter Secure Authentication Library
=========================================

This is a secure authentication library for codeigniter.

**WARNING**: this is version 2 of this library, a more simplified, easier to use version that is easier to implement in existing code. The original library relied too much on correct model communication that now has been removed. Most of the functionality has been preserved, although some things have been moved to the model as you can see in the example folder.

Installation
------------

Place the files from the repository in their respective folders (or use spark). A database.sql file is included containing the required database structure.

Configuration
-------------

Edit the auth.php configuration file to fit your specific environment:

    /*
    |--------------------------------------------------------------------------
    | Authentication configuration
    |--------------------------------------------------------------------------
    | The basic settings for the auth library.
    |
    | 'cookie_name'         = the name you want for the cookie
    | 'cookie_encrypt'   = encrypt cookie with encryption_key
    | 'autologin_expire' = time for cookie to expire in seconds (renews when used)
    | 'autologin_table'  = the name of the autologin table (see .sql file)
    | 'hash_algorithm'   = the hashing algorithm used for generating keys
    */

    $config['cookie_name']      = 'autologin';
    $config['cookie_encrypt']   = TRUE;
    $config['autologin_table']  = 'autologin';
    $config['autologin_expire'] = 5184000; // 60 days
    $config['hash_algorithm']   = 'sha256';

If you prefer, you can autoload the library by adjusting your autoload.php file and add 'auth' to the $autoload['libraries'] array.

Usage
-----

A simple implementation example of this library is included, so be sure to check out the demo folder. These are the available methods:

    $this->auth->login($id, $remember = TRUE)
Mark the user with this id as logged in, provide an optional remember boolean if you want to create an autologin cookie
    
    $this->auth->logout()
Logout function, this removes the autologin cookie and the active key

    $this->auth->loggedin()
Returns whether the user is logged in or not, TRUE/FALSE

    $this->auth->userid()
Returns the current user's id

Details & Security
------------------

This library was inspired by the following articles:

 - http://www.shinytype.com/php/persistent-login-protocol/
 - http://jaspan.com/improved_persistent_login_cookie_best_practice
 
When a user logs in with 'remember me' checked, a login cookie is created containing the user's identification and a personal key. Actually 2 keys are created, one for the user's cookie and one to store into the database. A user can only log in if both key pairs are present. 

When that user visits the site again, it presents the login cookie. The database version of the key is compared with the key stored in the cookie. If the relation between both keys is correct, the user is logged in, the used key pair will be removed and a new key pair is generated for future use.

If on the other hand, the key pair is invalid, a possible cookie/key theft assumed. The user's active key will then immediately be removed for safety reasons.

Controller example
------------------

In the demo folder you can find a fully working example of this library. It also includes a basic user model and an extra .sql script to create the users database table.

Here is an example how you _could_ use the library on your login page:

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
                $error = "Wrong password";
            }
        } else {
            $error = "User does not exist";
        }
    }