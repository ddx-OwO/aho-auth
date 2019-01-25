<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/Smartc_API.php');

class Authenticate extends Smartc_API {

    private $identity_column;

    public function __construct()
    {
        parent::__construct();

        $this->identity_column = $this->smartc_auth_model->identity_column;
    }

	public function index_post()
	{
        $identity = $this->post($this->identity_column, TRUE);
        $password = $this->post('password', TRUE);
        $rememberme = $this->post('rememberme', TRUE);

        if ($this->smartc_auth->login($identity, $password, $rememberme))
        {
            $res = $this->prepare_response([
                'message' => $this->smartc_auth->message()
            ], REST_Controller::HTTP_OK);
        }
        else
        {
            $res = $this->prepare_error_response(
                'unauthorized', 
                $this->smartc_auth->message(),
                REST_Controller::HTTP_UNAUTHORIZED
            );
        }

        $this->response($res, $res['code']);
	}

    /*public function index_get()
    {
        $identity = 'admin';
        $password = 'admin';
        $rememberme = TRUE;

        if ($this->smartc_auth->login($identity, $password, $rememberme))
        {
            $this->response([
                'message' => $this->smartc_auth->message()

            ], Smartc_API::HTTP_OK);
        }
        else
        {
            $this->response([
                'message' => $this->smartc_auth->message()
            ],Smartc_API::HTTP_UNAUTHORIZED, FALSE, TRUE);
        }
    }*/

    public function session_post()
    {
        $this->response([
            'is_logged_in' => $this->smartc_auth->is_logged_in(),
            'test' => $this->post('username', TRUE)
        ]);
    }

    public function logout_get()
    {
        $this->response($this->smartc_auth->logout());
    }
}
