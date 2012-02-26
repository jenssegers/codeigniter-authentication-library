<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is an EXAMPLE user model, edit to match your implementation
 * OR use the adapter model for easy integration with an existing model
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
        
        $user['password'] = $this->hash($user['password']);
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
            $user['password'] = $this->hash($user['password']);
        } else {
            unset($user['password']);
        }
        
        $this->db->where('id', $id)->update($this->table, $user);
        return $id;
    }
    
    /**
     * Delete a user
     * 
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function delete($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = 'id';
        }
        
        $this->db->where($where, $value)->delete($this->table);
    }
    
    /**
     * Retrieve a user
     * 
     * @param string where
     * @param int value
     * @param string identification field
     */
    public function get($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = 'id';
        }
        
        $user = $this->db->where($where, $value)->get($this->table)->row_array();
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
            return $this->db->order_by("username")->limit($limit, $offset)->get($this->table)->result_array();
        } else {
            return $this->db->order_by("username")->get($this->table)->result_array();
        }
    }
    
    /**
     * Check if a user exists
     * 
     * @param string where
     * @param int value
     * @param string identification field
     */
    
    public function exists($where, $value = FALSE) {
        if (!$value) {
            $value = $where;
            $where = 'id';
        }
        
        return $this->db->where($where, $value)->count_all_results($this->table);
    }
    
    /**
     * Password hashing function
     * 
     * @param string $password
     */
    public function hash($password) {
        $this->load->library('PasswordHash', array('iteration_count_log2' => 8, 'portable_hashes' => FALSE));
        
        // hash password
        return $this->passwordhash->HashPassword($password);
    }
    
    /**
     * Compare user input password to stored hash
     * 
     * @param string $password
     * @param string $stored_hash
     */
    public function check_password($password, $stored_hash) {
        $this->load->library('PasswordHash', array('iteration_count_log2' => 8, 'portable_hashes' => FALSE));
        
        // check password
        return $this->passwordhash->CheckPassword($password, $stored_hash);
    }
}