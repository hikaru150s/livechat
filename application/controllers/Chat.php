<?php
class Chat extends CI_Controller {
    public $user;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->database();
        $this->load->helper(array('url', 'form'));
        $this->load->library('user_agent');

		// If this controller accessed before login, redirect it back.
        if (!isset($this->session->userdata['logged_in']) || $this->session->userdata['logged_in'] === false) {
            redirect('welcome');
        }

        // Save current user data
		$this->user = $this->db->get_where('users', array('id' => $this->session->userdata['id']), 1)->row();
    }

    public function index() {
		// If user is customer service, get all another user. Otherwise, just get customer service
		if ($this->user->customer_service == 1) {
			$contact = $this->db->where('id !=', $this->user->id)->get('users');
		} else {
			$contact = $this->db->where('customer_service =', 1)->get('users');
		}
        $this->load->view('chat_dashboard', array(
            'contact' => $contact
        ));
    }

    public function getChats() {
        header('Content-Type: application/json');
        if ($this->input->is_ajax_request()) {
            // Find friend
            $friend = $this->db->get_where('users', array('id' => $this->input->post('chatWith')), 1)->row();

            // Get Chats
            $chats = $this->db
                ->select('chats.*, users.name')
                ->from('chats')
                ->join('users', 'chats.send_by = users.id')
                ->where('(send_by = '. $this->user->id .' AND send_to = '. $friend->id .')')
                ->or_where('(send_to = '. $this->user->id .' AND send_by = '. $friend->id .')')
                ->order_by('chats.time', 'desc')
                ->limit(100)
                ->get()
                ->result();

            $result = array(
                'name' => $friend->name,
                'chats' => $chats
            );
            echo json_encode($result);
        }
    }

    public function sendMessage() {
        $this->db->insert('chats', array(
            'messages' => htmlentities($this->input->post('messages', true)),
            'send_to' => $this->input->post('chatWith'),
            'send_by' => $this->user->id
        ));
    }
}