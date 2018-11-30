<?php
class Chats_model extends CI_Model {
	private $table;
	
	public function __construct() {
		$this->load->database();
		$this->table = 'chats';
	}
	
	public function get() {
		return $this->db->get($this->table);
	}
		
	public function getById($id) {
		return $this->db->get_where($this->table, array('id' => $id, 1)->row();
	}
	
	public function getChats($user, $friend) {
		return $this->db
                ->select('chats.*, users.name')
                ->from('chats')
                ->join('users', 'chats.send_by = users.id')
                ->where('(send_by = '. $user .' AND send_to = '. $friend .')')
                ->or_where('(send_to = '. $user .' AND send_by = '. $friend .')')
                ->order_by('chats.time', 'desc')
                ->limit(100)
                ->get()
                ->result();
	}
	
	public function count() {
		return $this->db->count_all($this->table);
	}
	
	public function insert($data) {
		return $this->db->insert($this->table, $data);
	}
}
