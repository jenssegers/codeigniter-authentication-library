<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

class Auth {

    /**
     * Constructor, loads dependencies and detects the autologin cookie
     */
    public function __construct()
    {
        $this->CI =& get_instance();

        // load session library
        $this->CI->load->library('session');

        log_message('debug', 'Authentication library initialized');

        // detect autologin
        if (!$this->CI->session->userdata('auth_loggedin'))
            $this->autologin();
    }
    // --------------------------------------------


    /**
     * Mark a user as logged in and create autologin cookie if wanted
     * remember to extend with your login information also
     *
     * @param string $id
     * @param boolean $remember
     * @return boolean
     */
    public function login($id = FALSE, $remember = TRUE)
    {
        if(!$id || !$this->loggedin())
        {
            // mark user as logged in
            $this->CI->session->set_userdata(array(
                                                    'auth_user'     => $id,
                                                    'auth_loggedin' => TRUE
                                                ));

            if ($remember)
                $this->create_autologin($id);
        }
    }
    // --------------------------------------------


    /**
     * Logout the current user, destroys the current session and autologin key
     */
    public function logout()
    {
        // destroy the session
        $this->CI->session->sess_destroy();

        // remove cookie and active key
        $this->delete_autologin();
    }
    // --------------------------------------------


    /**
     * Check if the current user is logged in or not
     *
     * @return boolean
     */
    public function loggedin()
    {
        return $this->CI->session->userdata('auth_loggedin');
    }
    // --------------------------------------------


    /**
     * Returns the user id of the current user when logged in
     *
     * @return int
     */
    public function userid()
    {
        return $this->loggedin() ? $this->CI->session->userdata('auth_user') : FALSE;
    }
    // --------------------------------------------


    /**
     * Generate a new key pair and create the autologin cookie
     *
     * @param int $id
     * @param string $series
     */
    private function create_autologin($id, $series = FALSE)
    {
        // generate keys
        list($public, $private) = $this->generate_keys();

        $this->CI->load->model('autologin_model');

        // create new series or expand current series
        if (!$series)
        {
            list($series) = $this->generate_keys();
            $this->CI->autologin_model->insert($id, $series, $private);
        }
        else
        {
            $this->CI->autologin_model->update($id, $series, $private);
        }

        // build the cookie data
        $cookie = array(
                            'id'        => $id,
                            'series'    => $series,
                            'key'       => $public
                        );

        // write public key to cookie
        $this->write_cookie($cookie);
    }
    // --------------------------------------------


    /**
     * Disable the current autologin key and remove the cookie
     */
    private function delete_autologin()
    {
        if ($cookie = $this->read_cookie())
        {
            // remove current series
            $this->CI->load->model('autologin_model');
            $this->CI->autologin_model->delete($cookie['id'], $cookie['series']);

            // delete cookie
            $this->CI->input->set_cookie(array(
                                            'name'      => $this->cookie_name,
                                            'value'     => '',
                                            'expire'    => ''
                                        ));
        }
    }
    // --------------------------------------------


    /**
     * Detects the autologin cookie and check public/private key pair
     *
     * @return boolean
     */
    private function autologin()
    {
        if ($cookie = $this->read_cookie())
        {
            // remove expired keys
            $this->CI->load->model('autologin_model');
            $this->CI->autologin_model->purge();

            // get private key
            $private = $this->CI->autologin_model->get($cookie['id'], $cookie['series']);

            if ($this->validate_keys($cookie['key'], $private))
            {
                // mark user as logged in
                $this->CI->session->set_userdata(array(
                                                    'auth_user'     => $cookie['id'],
                                                    'auth_loggedin' => TRUE
                                                ));

                // user has a valid key, extend current series with new key
                $this->create_autologin($cookie['id'], $cookie['series']);

                return TRUE;
            }
            else
            {
                // the key was not valid, strange stuff going on
                // remove the active session to prevent theft!
                $this->delete_autologin();
            }
        }

        return FALSE;
    }
    // --------------------------------------------


    /**
     * Write data to autologin cookie
     *
     * @param array $data
     */
    private function write_cookie($data = array())
    {
        $data = serialize($data);

        // encrypt cookie
        if ($this->cookie_encrypt)
        {
            $this->CI->load->library('encrypt');
            $data = $this->CI->encrypt->encode($data);
        }

        return $this->CI->input->set_cookie(array(
                                                'name'      => $this->cookie_name,
                                                'value'     => $data,
                                                'expire'    => $this->autologin_expire
                                            ));
    }
    // --------------------------------------------


    /**
     * Read data from autologin cookie
     *
     * @return boolean
     */
    private function read_cookie()
    {
        $cookie = $this->CI->input->cookie($this->cookie_name, TRUE);

        if (!$cookie)
            return FALSE;

        // decrypt cookie
        if ($this->cookie_encrypt)
        {
            $this->CI->load->library('encrypt');
            $data = $this->CI->encrypt->decode($cookie);
        }

        $data = @unserialize($data);

        if (isset($data['id']) && isset($data['series']) && isset($data['key']))
            return $data;

        return FALSE;
    }
    // --------------------------------------------


    /**
     * Generate public/private key pair
     *
     * @return array
     */
    private function generate_keys()
    {
        $public = hash($this->hash_algorithm, uniqid(rand()));
        $private = hash_hmac($this->hash_algorithm, $public, $this->fetch_key());

        return array($public, $private);
    }
    // --------------------------------------------


    /**
     * Validate public/private key pair
     *
     * @param string $public
     * @param string $private
     * @return boolean
     */
    private function validate_keys($public, $private)
    {
        $check = hash_hmac($this->hash_algorithm, $public, $this->fetch_key());
        return ($check === $private);
    }
    // --------------------------------------------


    /**
     * Get the encryption key from the system, and ensure it is exactly 64 bytes
     *
     * @return string
     */
    private function fetch_key()
    {
    	// fetch the encryption key from the application
    	$key = $this->CI->config->item('encryption_key');

	    // adjust key to exactly 64 bytes
	    if (strlen($key) > 64)
	        $key = str_pad(sha1($key, true), 64, chr(0));
	    if (strlen($key) < 64)
	        $key = str_pad($key, 64, chr(0));

        return $key;
    }
    // --------------------------------------------


}

// eof.
