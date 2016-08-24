<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hello extends CI_Controller{

  public function __construct(){
  	
	parent::__construct();
	echo "This is the initialization<br />";
	
  }

  public function  index(){
  	
	echo "This is my index function...";
  	
  }
  
  /* Might use this later....avendanl
  public function one($p1,$p2) {
  	
	echo "This is the one method called from the Hello controller...It works!<br />";
	echo "These are the params: $p1, $p2"; 

  }
  */
  
  public function one($name) {
  	
	$this->load->model("hello_model");
	
	$profile = $this->hello_model->getProfile("Luciano");
	//print_r($profile);
	

	$this->load->view('includes/header');
	
	//$data = array("name" => $name);
	$data['profile'] = $profile;
	$this->load->view('one', $data);
	
	$this->load->view('includes/footer');
    
  }
  
  public function two(){
  	
	echo "This is my function two...";
	
  }

}
