<?php
class Chat extends CI_Controller {
    public $user;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->database();
        $this->load->helper(array('url', 'form'));
        $this->load->library('user_agent');
	$this->load->library('CryptoChat');

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
			
			// Decode all messages first
			/*foreach($chats as &$chat) { // Compatibility issue persist
				if ($chat->send_to == $this->user->id) {
					$chat->messages = $this->cryptochat->decrypt($chat->messages, $this->user->priv);
				} else {
					$chat->messages = $this->cryptochat->decrypt($chat->messages, $friend->priv);
				}
			}*/
			// Compatibly decode all messages
			$keys = array_keys($chats); // Deals with both numeric and associative keys
			foreach($keys as $k) {
				if ($chats[$k]->send_to == $this->user->id) {
					$chats[$k]->messages = $this->cryptochat->decrypt($chats[$k]->messages, $this->user->priv);
				} else {
					$chats[$k]->messages = $this->cryptochat->decrypt($chats[$k]->messages, $friend->priv);
				}
			}

            $result = array(
                'name' => $friend->name,
                'chats' => $chats
            );
            echo json_encode($result);
        }
    }

    public function sendMessage() {
		// Get send_to (receiver) public key
		$pub = $this->db->get_where('users', array('id' => $this->input->post('chatWith')), 1)->row()->pub;
		// Encrypt the messages
		$ciphertext = $this->cryptochat->encrypt(htmlentities($this->input->post('messages', true)), $pub);
		// Add to db
        $this->db->insert('chats', array(
            'messages' => $ciphertext,
            'send_to' => $this->input->post('chatWith'),
            'send_by' => $this->user->id
        ));
    }
}
