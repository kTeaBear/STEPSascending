<?php
/**
 * 
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
	
	// majorInfo() is called from an AJAX POST from the dashboard view.
	// param: $id is required for the dahboard_model to make the db call
	// and find the correct db query.
	// 
	public function majorInfo() {
		
		$this->load->model('dashboard_model');
		$id=$this->input->post('id');
		
		$query = $this->dashboard_model->loadMajorInfo($id);
		
		// Debug statement.
		//print_r($query);
		if ($query){
		  $data = array(
		    'major'       => $query[0]['major'],
		    'submajor'    => $query[0]['submajor'],
		    'description' => $query[0]['description']
		  );
		}
		//print_r($data);  
		echo "<h3>" . $data['major'] . " -> " . $data['submajor'] . "</h3>";
		echo "<p>" . $data['description'] . "</p>";
		
	}
	
	// careerInfo() is called from an AJAX POST from the dashboard view.
	// param: $id is required for the dahboard_model to make the db call
	// and find the correct db query.
	//
	public function careerInfo() {
		
		$this->load->model('dashboard_model');
		$id=$this->input->post('id');
		
		$query = $this->dashboard_model->loadCareerInfo($id);
		
		// Debug statement.
		//print_r($query);
		if ($query){
		  $data = array(
		    'career'       => $query[0]['career'],
		    'subcareer'    => $query[0]['subcareer'],
		    'description' => $query[0]['description']
		  );
		}
		
		echo "<h3>" . $data['career'] . " -> " . $data['subcareer'] . "</h3>";
		echo "<p>" . $data['description'] . "</p>";
		
	}
	
	
	
}

?>