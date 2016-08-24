<?php
/**
 * 
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {
	// getMajors() returns a list of id, submajors in object form.
	// 
	function getMajors(){
		// Return the Id and Submajor fields to display in dashboard.
		$query = $this->db->query('SELECT id, submajor FROM major_category');
		return $query->result();
		
	}
	
	// loadMajorInfo($id) returns the id, major, submajor, and description fields.
	// these are returned in an array form.
	// param: $id is passed from a view through an AJAX call
	//
	function loadMajorInfo($id) {
		
		$this->db->select("id, major, submajor, description");
		$this->db->where('id', $id);
		$query = $this->db->get('major_category');
		
		if($query->num_rows() == 1) {
			
			return $query->result_array();
			
		} 
		
	}
	
	// get Careers() returns a list of id, subcareers in object form.
	//
	function getCareers(){
		// Return the Id and Submajor fields to display in dashboard.
		$query = $this->db->query('SELECT id, subcareer FROM career_category');
		return $query->result();
		
	}
	
	// localCareerInfo($id) returns id, career, subcareer, description
	// in array form.
	//param: $id is passed from a view through an AJAX call
	//
	function loadCareerInfo($id) {
		
		$this->db->select("id, career, subcareer, description");
		$this->db->where('id', $id);
		$query = $this->db->get('career_category');
		
		if($query->num_rows() == 1) {
			
			return $query->result_array();
			
		} 
		
	}
	
	
}

?>	