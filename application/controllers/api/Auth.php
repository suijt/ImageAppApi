<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

// Load the Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';

class Auth extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Load the user model
        $this->load->model('user');
    }

    public function login_post()
    {
        // Get the post data
        $email = $this->post('email');
        $password = $this->post('password');

        // Validate the post data
        if (!empty($email) && !empty($password)) {

            // Check if any user exists with the given credentials
            $con['returnType'] = 'single';
            $con['conditions'] = array(
                'email' => $email,
                'password' => md5($password),
                'status' => 1
            );
            $user = $this->user->getRows($con);

            if ($user) {
                $this->db->select('*');
                $this->db->from('keys');
                $this->db->where('user_id', $user['id']);
                $query = $this->db->get();
                $result = $query->row_array();
                //    print_r($result);die();
                // Set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'User login successful.',
                    'data' => $user,
                    'token' => $result['key']
                ], REST_Controller::HTTP_OK);
            } else {
                // Set the response and exit
                //BAD_REQUEST (400) being the HTTP response code
                $this->response("Wrong email or password.", REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            // Set the response and exit
            $this->response("Provide email and password.", REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function user_get($id = 0)
    {
        // Returns all the users data if the id not specified,
        // Otherwise, a single user will be returned.
        $con = $id ? array('id' => $id) : '';
        $users = $this->user->getRows($con);

        // Check if the user data exists
        if (!empty($users)) {
            // Set the response and exit
            //OK (200) being the HTTP response code
            $this->response($users, REST_Controller::HTTP_OK);
        } else {
            // Set the response and exit
            //NOT_FOUND (404) being the HTTP response code
            $this->response([
                'status' => FALSE,
                'message' => 'No user was found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function verify_get()
    {
        $token = $_GET['token'];
        $this->db->select('*');
        $this->db->from('keys');
        $this->db->where('key', $token);
        $query = $this->db->get();
        //    print_r($query->num_rows);die();
        if ($query->num_rows() > 0) {
            $user = $this->user->getRows(array('id' => '1'));
            $this->response([
                'status' => TRUE,
                'token' => $token,
                'user' => $user,
                'message' => 'Token Verified'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response("Token Mismatch", REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
