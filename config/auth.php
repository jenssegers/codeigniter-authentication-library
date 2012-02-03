<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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