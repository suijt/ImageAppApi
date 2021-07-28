<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

// Load the Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';

class Image extends REST_Controller {

    public function __construct() { 
        parent::__construct();        
        // Load the image model
        $this->load->model('imageModel','image');
    }
        
    public function image_post() {
        // Get the post data
        $title = strip_tags($this->post('image_title'));
        $description = strip_tags($this->post('image_description'));
        // $image = strip_tags($this->post('image'));
// print_r($_FILES['image']);die();
        if(!empty($_FILES['image']['name'])){

           // Set preference 
        //    $config['upload_path'] = 'uploads/images/'; 
           $config['upload_path']   = APPPATH. '../uploads/images/';
           $config['allowed_types'] = 'jpg|jpeg|png|gif'; 
           $config['max_size']      = '900'; // max_size in kb 
           $config['image_name']    = $_FILES['image']['name']; 
  
           // Load upload library 
           $this->load->library('upload',$config); 
     
           // File upload
           if($this->upload->do_upload('image')){ 
              // Get data about the image
              $uploadData = $this->upload->data();
              $imagename = $uploadData['file_name']; 
           }else{ 
                $imagename = ''; 
           } 
        }else{ 
            $imagename = ''; 
        } 

        // Validate the post data
        if(!empty($title) && !empty($description) && !empty($imagename)){
                                   
                // Insert image data
                $imageData = array(
                    'title' => $title,
                    'description' => $description,
                    'image' => $imagename,
                    'status' => 1
                );
                $insert = $this->image->insert($imageData);
                
                // Check if the image data is inserted
                if($insert){
                    // Set the response and exit
                    $this->response([
                        'status' => TRUE,
                        'message' => 'The image has been added successfully.',
                        'data' => $insert
                    ], REST_Controller::HTTP_OK);
                }else{
                    // Set the response and exit
                    $this->response("Some problems occurred, please try again.", REST_Controller::HTTP_BAD_REQUEST);
                }
        }else{
            // Set the response and exit
            $this->response("Provide complete image info to add.", REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function image_get($id = 0) {
        // Returns all the images data if the id not specified,
        // Otherwise, a single image will be returned.
        $con = $id?array('id' => $id):'';
        // print_r($con);die;
        $images = $this->image->getRows($con);
        
        // Check if the image data exists
        if(!empty($images)){
            // Set the response and exit
            //OK (200) being the HTTP response code
            $this->response($images, REST_Controller::HTTP_OK);
        }else{
            // Set the response and exit
            //NOT_FOUND (404) being the HTTP response code
            $this->response([
                'status' => FALSE,
                'message' => 'No image was found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
    
    public function image_put() {
        $id = $this->put('id');
        
        // Get the post data
        $title = strip_tags($this->put('title'));
        $description = strip_tags($this->put('description'));
        $image = strip_tags($this->put('image'));
        $status = strip_tags($this->put('status'));
        
        // Validate the post data
        if(!empty($id) && (!empty($title) || !empty($description) || !empty($image) || !empty($status))){
            // Update image's account data
            $imageData = array();
            if(!empty($title)){
                $imageData['title'] = $title;
            }
            if(!empty($description)){
                $imageData['description'] = $description;
            }
            if(!empty($image)){
                $imageData['image'] = $image;
            }
            if(!empty($status)){
                $imageData['status'] = $status;
            }
            $update = $this->image->update($imageData, $id);
            
            // Check if the image data is updated
            if($update){
                // Set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'The image info has been updated successfully.'
                ], REST_Controller::HTTP_OK);
            }else{
                // Set the response and exit
                $this->response("Some problems occurred, please try again.", REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            // Set the response and exit
            $this->response("Provide at least one image info to update.", REST_Controller::HTTP_BAD_REQUEST);
        }
    }

}