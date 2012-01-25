<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model
{
	public function insert($user) {
		$user['password'] = $this->auth->hash($user['password']);
		$this->db->insert('users', $user);
		return $this->db->insert_id();
	}
	
	public function update($id, $user) {
		if(isset($user['password']) && $user['password'])
			$user['password'] = $this->auth->hash($user['password']);
		else
			unset($user['password']);
			
		$this->db->where('id', $id)->update('users', $user);
		return $id;
	}
	
	public function delete($id) {
		$this->db->where('id', $id)->delete('users');
	}
	
	public function get($key, $where='id') {
		return $this->db->where($where, $key)->get('users')->row_array();
	}
	
	public function activate($id) {
		return $this->update($id, array('activated'=>1));
	}
	
	public function deactivate($id) {
		return $this->update($id, array('activated'=>0));
	}

}