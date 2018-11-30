<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->library(array('session', 'rsa'));
		$this->load->model('users_model');
		$this->load->helper(array('url', 'form'));
	}

	public function index() {
		// Initialize data handler
		$data = array();
		$data['err'] = ''; // Means no error
		$data['info'] = ''; // Means no info now
		if (isset($_POST['login'])) { // Is user click login?
			$username = $this->input->post('username', true);
			$password = $this->input->post('password', true);
			if ($username !== '' && $password !== '') {
				// Verify username and password
				$db = $this->users_model->getByUsername($username);
				if ($db !== null & $db !== false && password_verify($password, $db->password)) {
					$userdata = array(
						'id'	=> $db->id,
						'username'	=> $username,
						'logged_in'	=> true
					);
					$this->session->set_userdata($userdata);
					redirect('chat', 'refresh');
				} else {
					// Throw error
					$data['err'] = "Username not found or password mismatch!";
				}
			} else {
				// Throw error
				$data['err'] = "Do not leave Username and Password blank!";
			}
		} else if (isset($_POST['register'])) { // Or user click register?
			$username = $this->input->post('username', true);
			$name = $this->input->post('name', true);
			$password = $this->input->post('password', true);
			// Check username
			$this->db->where('username', $username)->from('users');
			if ($this->db->count_all_results() == 0) { // Username not exist, create new
				// Hash the password (using Blowfish algorithm with default options, return 60 char string)
				$hashedpassword = password_hash($password, PASSWORD_BCRYPT);
				// Generate pubilc and private key (Using OpenSSL)
				// It is required to include the PHP path to environment variables to use this plugin
				$pub = '';
				$priv = '';
				$pair = $this->rsa->generate();
				$pub = $pair['public'];
				$priv = $pair['private'];
				
				// Wait there! Need some trick here ...
				$status = false;
				if ($this->users_model->count() == 0) { // If there are no user, ...
					// Add to db
					$status = $this->users_model->insert(array(
						'username'			=> $username,
						'name'				=> $name,
						'password'			=> $hashedpassword,
						'pub'				=> $pub,
						'priv'				=> $priv,
						'customer_service'	=> 1 // Make this user as customer service
					));
				} else { // Otherwise just add as regular user
					// Add to db
					$status = $this->users_model->insert(array(
						'username'			=> $username,
						'name'				=> $name,
						'password'			=> $hashedpassword,
						'pub'				=> $pub,
						'priv'				=> $priv
					));
				}
				
				// Notify
				if ($status) {
					$data['info'] = "Register successful! You can login now.";
				} else {
					$data['err'] = "Database error! " . $this->db->error();
				}
			} else { // Username exist, throw error
				$data['err'] = "Username already taken!";
			}
			
		}
		// On any failure, return back to here
		$this->load->view('login', $data);
	}
	
	public function logout() {
		$this->session->sess_destroy();
		redirect('welcome');
	}
}
