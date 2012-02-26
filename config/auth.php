<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| Authentication configuration
|--------------------------------------------------------------------------
| The basic settings for the auth library.
|
| 'cookie_name'	     = the name you want for the cookie
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