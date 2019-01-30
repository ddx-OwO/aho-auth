<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    private $identity_column;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('aho_auth_model', 'aho_auth');
        $this->identity_column = $this->aho_auth->identity_column;
    }

	public function index_post()
	{
        $identity = $this->post($this->identity_column, TRUE);
        $password = $this->post('password', TRUE);
        $rememberme = $this->post('rememberme', TRUE);
        $token = $this->aho_auth->login($identity, $password, $rememberme);

        if ($token !== FALSE)
        {
            $_response = prep_response([
                'message' => $this->message->message_string(),
            ], REST_Controller::HTTP_OK);
            $_response['data'] = array_merge($_response['data'], $token);
        }
        else
        {
            $_response = prep_response([
                'type' => 'bad_credentials',
                'message' => $this->message->message_string()
            ], REST_Controller::HTTP_UNAUTHORIZED, TRUE);      
        }

        $this->response($_response, $_response['code']);
	}

    public function token_post()
    {
        $refresh_token = $this->post('refresh_token', TRUE);
        $user_id = $this->post('user_id', TRUE);

        if (empty($refresh_token) || empty($user_id))
        {
            $_response = prep_response([
                'type' => 'bad_request',
                'message' => 'Mismatch fields'
            ], REST_Controller::HTTP_BAD_REQUEST, TRUE);

            $this->response($_response, $_response['code']);
        }

        $token = $this->aho_auth->login_refresh($refresh_token, $user_id);

        if ($token !== FALSE)
        {
            $_response = prep_response($token, REST_Controller::HTTP_OK);
        }
        else
        {
            $_response = prep_response([
                'type' => 'invalid_token',
                'message' => $this->message->message_string()
            ], REST_Controller::HTTP_UNAUTHORIZED, TRUE);
        }
        $this->response($_response, $_response['code']);
    }

    public function csrf_get()
    {
        $_response = prep_response([
            'csrf_token' => $this->security->get_csrf_hash()
        ], REST_Controller::HTTP_OK);

        $this->response($_response, $_response['code']);
    }
}
