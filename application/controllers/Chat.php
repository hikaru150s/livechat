<?php
class Chat extends CI_Controller {
    public $user;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('users_model');
		$this->load->model('chats_model');
        $this->load->helper(array('url', 'form'));
        $this->load->library('user_agent');
		$this->load->library('CryptoChat');

		// If this controller accessed before login, redirect it back.
        if (!isset($this->session->userdata['logged_in']) || $this->session->userdata['logged_in'] === false) {
            redirect('welcome');
        }

        // Save current user data
		$this->user = $this->users_model->getById($this->session->userdata['id']);
    }

    public function index() {
		// If user is customer service, get all another user. Otherwise, just get customer service
		if ($this->user->customer_service == 1) {
			$contact = $this->users_model->getContactList();
		} else {
			$contact = $this->users_model->getCustomerService();
		}
        $this->load->view('chat_dashboard', array(
            'contact' => $contact
        ));
    }

    public function getChats() {
        header('Content-Type: application/json');
        if ($this->input->is_ajax_request()) {
            // Find friend
			$friend = $this->users_model->getById($this->input->post('chatWith'));

            // Get Chats
			$chats = $this->chats_model->getChats($this->user->id, $friend->id);
			
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
		$pub = $this->users_model->getById($this->input->post('chatWith'))->pub;
		// Encrypt the messages
		$ciphertext = $this->cryptochat->encrypt(htmlentities($this->input->post('messages', true)), $pub);
		// Add to db
        $this->chats_model->insert(array(
            'messages' => $ciphertext,
            'send_to' => $this->input->post('chatWith'),
            'send_by' => $this->user->id
        ));
    }
}
