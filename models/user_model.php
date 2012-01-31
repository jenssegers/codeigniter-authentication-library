<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is an example user model, edit to match your implementation
 */
class User_model extends CI_Model {
    
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
        
        $this->db->insert('users', $user);
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
        
        $this->db->where('id', $id)->update('users', $user);
        return $id;
    }
    
    /**
     * Delete a user
     * 
     * @param int key
     * @param string identification field
     */
    public function delete($key, $where = 'id') {
        $this->db->where($where, $key)->limit(1)->delete('users');
    }
    
    /**
     * Retrieve a user
     * 
     * @param int key
     * @param string identification field
     */
    public function get($key, $where = 'id') {
        $user = $this->db->where($where, $key)->get('users')->row_array();
        return $user;
    }
    
    /**
     * Get a list of users with pagination options
     * 
     * @param int limit
     * @param int offset
     * @return array users
     */
    public function get_list($limit = null, $offset = null) {
        return $this->db->order_by("name")->get("users")->limit($limit, $offset)->result_array();
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