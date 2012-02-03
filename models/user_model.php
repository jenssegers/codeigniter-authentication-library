<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is an example user model, edit to match your implementation
 */
class User_model extends CI_Model {
    
    // database table name
    var $table = 'users';
    
    /**
     * Add a user, password will be hashed
     * 
     * @param array user
     * @return int id
     */
    public function insert($user) {
        // need the library for hashing the password
        $this->load->library("auth");
        
        $user['password'] = $this->auth->hash($user['password']);
        $user['registered'] = time();
        
        $this->db->insert($this->table, $user);
        return $this->db->insert_id();
    }
    
    /**
     * Update a user, password will be hashed
     * 
     * @param int id
     * @param array user
     * @return int id
     */
    public function update($id, $user) {
        // prevent overwriting with a blank password
        if (isset($user['password']) && $user['password']) {
            // need the library for hashing the password
            $this->load->library("auth");
            $user['password'] = $this->auth->hash($user['password']);
        } else {
            unset($user['password']);
        }
        
        $this->db->where('id', $id)->update($this->table, $user);
        return $id;
    }
    
    /**
     * Delete a user
     * 
     * @param int key
     * @param string identification field
     */
    public function delete($key, $where = 'id') {
        $this->db->where($where, $key)->delete($this->table);
    }
    
    /**
     * Retrieve a user
     * 
     * @param int key
     * @param string identification field
     */
    public function get($key, $where = 'id') {
        $user = $this->db->where($where, $key)->get($this->table)->row_array();
        return $user;
    }
    
    /**
     * Get a list of users with pagination options
     * 
     * @param int limit
     * @param int offset
     * @return array users
     */
    public function get_list($limit = FALSE, $offset = FALSE) {
        if ($limit) {
            return $this->db->order_by("name")->limit($limit, $offset)->get($this->table)->result_array();
        } else {
            return $this->db->order_by("name")->get($this->table)->result_array();
        }
    }
    
    /**
     * Check if a user exists
     * 
     * @param int key
     * @param string identification field
     */
    
    public function exists($key, $where = 'id') {
        return $this->db->where($where, $key)->get($this->table)->num_rows();
    }
    
    /**
     * Activate a user
     * 
     * @param int id
     */
    public function activate($id) {
        return $this->update($id, array('activated' => 1));
    }
    
    /**
     * Deactivate a user
     * 
     * @param int id
     */
    public function deactivate($id) {
        return $this->update($id, array('activated' => 0));
    }

}