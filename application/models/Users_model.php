<?php
class Users_model extends CI_Model {
	private $table;
	
	public function __construct() {
		$this->load->database();
		$this->table = 'users';
	}
	
	public function get() {
		return $this->db->get($this->table);
	}
	
	public function getByUsername($username) {
		return $this->db->get_where($this->table, array('username' => $username), 1)->row();
	}
	
	public function getById($id) {
		return $this->db->get_where($this->table, array('id' => $id, 1)->row();
	}
	
	public function getCustomerService() {
		return $this->db->where('customer_service =', 1)->get($this->table);
	}
	
	public function getContactList() {
		return $this->db->where('id !=', $this->user->id)->get($this->table);
	}
	
	public function count() {
		return $this->db->count_all($this->table);
	}
	
	public function insert($data) {
		return $this->db->insert($this->table, $data);
	}
}
