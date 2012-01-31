<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is the autologin model used by the Authentication library
 * It handles interaction with the database to store autologin keys
 */
class Autologin_model extends CI_Model {
    
    public function exists($user, $key) {
        $this->db->where('user', $user);
        $this->db->where('key', $key);
        $query = $this->db->get('users_autologin');
        
        return $query->num_rows();
    }
    
    public function update($user, $old_key, $new_key) {
        $this->db->where('user', $user);
        $this->db->where('key', $old_key);
        
        return $this->db->update('users_autologin', array(
        	'key' => $new_key,
        	'used' => time(),
        	'ip' => $this->input->ip_address()));
    }
    
    public function insert($user, $key) {
        return $this->db->insert('users_autologin', array(
        	'user' => $user,
        	'key' => $key,
        	'used' => time(),
        	'ip' => $this->input->ip_address()));
    }
    
    public function clean($older_than) {
        $this->db->where('used <', $older_than)->delete('users_autologin');
    }
    
    public function purge($user) {
        $this->db->where('user', $user);
        $this->db->where('ip', $this->input->ip_address());
        $this->db->delete('users_autologin');
    }
    
    public function delete($user, $key) {
        $this->db->where('user', $user);
        $this->db->where('key', $key);
        $this->db->delete('users_autologin');
    }

}