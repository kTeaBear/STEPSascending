<?php

class Membership_model extends CI_Model {
	
	function validate(){
		
		$this->db->select("first_name, last_name, student_id");
		$this->db->where('email', $this->input->post('email'));
		$this->db->where('password', sha1($this->input->post('password')));
		$query = $this->db->get('users');
	    //$users = array();
		
		//echo print_r($query);
		if($query->num_rows() == 1) {
			//$row = $query->row();
			return $query->result_array();
			return true;
		} 
		
	}
	
	function create_member(){
		
		//$username = $this->input->post('first_name');
		
		$new_member_insert_data = array(
		        'first_name' => $this->input->post('first_name'),
				'last_name'  => $this->input->post('last_name'),
				'email'      => $this->input->post('email'),
				'password'   => sha1($this->input->post('password'))
			);
			
		$insert = $this->db->insert('users', $new_member_insert_data);
		return $insert;
	}
	
	function check_if_username_exists($username) {
		
		$this->db->where('username', $username);
		$result = $this->db->get('users');
		
		if ($result->num_rows() > 0) {
			return FALSE; // username in use
		} else {
			return TRUE; // username available
		}	
	}
	
	function check_if_email_exists($email) {
		
		$this->db->where('email', $email);
		$result = $this->db->get('users');
		
		if ($result->num_rows() > 0) {
			return FALSE; // email in use
		} else {
			return TRUE; // email not used
		}
		
	}
	
	
}
