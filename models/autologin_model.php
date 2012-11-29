<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is the autologin model used by the Authentication library
 * It handles interaction with the database to store autologin keys
 */
class Autologin_model extends CI_Model {

    /**
     * Get the settings from config
     */
    public function __construct()
    {
        $this->config->load('auth');

        $this->cookie_name      = $this->config->item('autologin_cookie_name');
        $this->cookie_encrypt   = $this->config->item('autologin_cookie_encrypt');
        $this->table            = $this->config->item('autologin_table');
        $this->expire           = $this->config->item('autologin_expire');
        $this->hash_algorithm   = $this->config->item('autologin_hash_algorithm');
    }

    /**
     * Get the private key for a specific user and series
     */
    public function get($user, $series)
    {
        $this->db->where('user', $user);
        $this->db->where('series', $series);
        $row = $this->db->get($this->table)->row();

        return $row ? $row->key : FALSE;
    }

    /**
     * Extend a user's current series with a new key
     */
    public function update($user, $series, $private)
    {
        $this->db->where('user', $user);
        $this->db->where('series', $series);

        $update_data = array(
                            'key'       => $private,
                            'created'   => time()
                        );

        return $this->db->update($this->table, $update_data);
    }

    /**
     * Start a new serie for a user
     */
    public function insert($user, $series, $private)
    {
        return $this->db->insert($this->table, array(
                                                    'user'      => $user,
                                                    'series'    => $series,
                                                    'key'       => $private,
                                                    'created'   => time()
                                                ));
    }

    /**
     * Dlete a user's series
     */
    public function delete($user, $series)
    {
        $this->db->where('user', $user);
        $this->db->where('series', $series);

        return $this->db->delete($this->table);
    }

    /**
     * Remove all expired keys
     */
    public function purge()
    {
        $this->db->where('created <', time() - $this->expire)
                 ->delete($this->table);
    }

}

// eof.
