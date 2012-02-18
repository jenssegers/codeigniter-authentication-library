<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is the autologin model used by the Authentication library
 * It handles interaction with the database to store autologin keys
 */
class Autologin_model extends CI_Model {
    
    // database table name
    var $table = 'users_autologin';
    
    /**
     * Check if a key is bound to a user
     */
    public function exists($user, $key) {
        $this->db->where('user', $user);
        $this->db->where('key', $key);
        $query = $this->db->get($this->table);
        
        return $query->num_rows();
    }
    
    /**
     * Update a user's key with a new key
     */
    public function update($user, $old_key, $new_key) {
        $this->db->where('user', $user);
        $this->db->where('key', $old_key);
        
        return $this->db->update($this->table, array(
        	'key' => $new_key,
        	'used' => time(),
        	'ip' => $this->input->ip_address()));
    }
    
    /**
     * Bind a new key to a user
     */
    public function insert($user, $key) {
        return $this->db->insert($this->table, array(
        	'user' => $user,
        	'key' => $key,
        	'used' => time(),
        	'ip' => $this->input->ip_address()));
    }
    
    /**
     * Clean all keys older than given timespan
     */
    public function clean($older_than) {
        $this->db->where('used <', $older_than)->delete($this->table);
    }
    
    /**
     * Remove all keys bound to this user's ip address
     */
    public function purge($user) {
        $this->db->where('user', $user);
        $this->db->where('ip', $this->input->ip_address());
        $this->db->delete($this->table);
    }
    
    /**
     * Delete a key bound to a user
     */
    public function delete($user, $key) {
        $this->db->where('user', $user);
        $this->db->where('key', $key);
        $this->db->delete($this->table);
    }

}