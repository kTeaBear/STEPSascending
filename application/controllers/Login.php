<?php
/**
 * Login - This is the main controller for the Login form. Users can register a new
 *         account or Sign In to existing accounts through here.
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
//defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {
	
	function index(){
		
		$this->load->view('includes/header');
		$this->load->view('login_form');
		$this->load->view('includes/footer');
		
	}
	
	function validate_creds() {
		
		$this->load->model('membership_model');
		$query = $this->membership_model->validate();
		 
		if ($query) {
			$data = array(
			  'username' => $this->input->post('username'),
			  'is_logged_in' => true
			);
			
			$this->session->set_userdata($data);
			//redirect('user_area');
			echo "<p>Hello World</p>";
		} else { // incorrect username or password
			
			$this->index();
		}
		
	}
	
	function register(){
		
		$this->load->view('includes/header');
		$this->load->view('register_form');
		$this->load->view('includes/footer');
	}
	
	function create_user(){
		
		$this->load->library('form_validation');
		
		// run validation rules
		$this->form_validation->set_rules('first_name', 'Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|callback_check_if_email_exists');
		$this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[4]|max_length[10]|callback_check_if_username_exists');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim|required|matches[password]');
		
		if ($this->form_validation->run() == FALSE) { // fails to validate.
			
			$this->load->view('includes/header');
			$this->load->view('register_form');
			$this->load->view('includes/footer');
			
		} else {
			
			$this->load->model('membership_model');
			if ($query = $this->membership_model->create_member()){
			
			    $data['account_created'] = "Your account has been created.<br /><br />You may now login.";
			    
			    $this->load->view('includes/header');
			    $this->load->view('login_form', $data);
				$this->load->view('includes/footer');
				
			} else {
				
				$this->load->view('includes/header');
			    $this->load->view('register_form');
				$this->load->view('includes/footer');
				
			}
		}
		
	}
	
	// Custom callback function for username. Checks to see if username exists in db
	function check_if_username_exists($requested_username){
		
		$this->load->model('membership_model');
		
		$username_available = $this->membership_model->check_if_username_exists($requested_username);
		if ($username_available){
			return TRUE;
		} else {
			return FALSE;
		}
		
	}
	
	// Custom callback function for email address.
	function check_if_email_exists($requested_email){
		
		$this->load->model('membership_model');
		
		$email_not_used = $this->membership_model->check_if_email_exists($requested_email);
		if ($email_not_used){
			return TRUE;
		} else {
			return FALSE;
		}
		
	}
}
?>