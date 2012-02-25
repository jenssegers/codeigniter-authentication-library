<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is the autologin model used by the Authentication library
 * It handles interaction with the database to store autologin keys
 */
class Autologin_model extends CI_Model {
    
    // database table name
    var $table = 'autologin';
    var $expire = 5184000;
    
    /**
     * Get the settings from config
     */
    public function __construct() {
        $this->config->load('auth');
        $this->table = $this->config->item('autologin_table');
        $this->expire = $this->config->item('autologin_expire');
    }
    
    /**
     * Get the private key for a specific user and series
     */
    public function get($user, $series) {
        $this->db->where('user', $user);
        $this->db->where('series', $series);
        $row = $this->db->get($this->table)->row();
        
        return $row ? $row->key : FALSE;
    }
    
    /**
     * Extend a user's current series with a new key
     */
    public function update($user, $series, $private) {
        $this->db->where('user', $user);
        $this->db->where('series', $series);
        
        return $this->db->update($this->table, array('key' => $private, 'created' => time()));
    }
    
    /**
     * Start a new serie for a user
     */
    public function insert($user, $series, $private) {
        return $this->db->insert($this->table, array('user' => $user, 'series' => $series, 'key' => $private, 'created' => time()));
    }
    
    /**
     * Dlete a user's series
     */
    public function delete($user, $series) {
        $this->db->where('user', $user);
        $this->db->where('series', $series);
        
        return $this->db->delete($this->table);
    }
    
    /**
     * Remove all expired keys
     */
    public function purge() {
        $this->db->where('created <', time() - $this->expire)->delete($this->table);
    }
}