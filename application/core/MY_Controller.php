<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;

class MY_Controller extends REST_Controller {

    const DEFAULT_LIMIT = 50;

    public function __construct($config = 'rest')
    {
        parent::__construct($config = 'rest');

        // header('Access-Control-Allow-Credentials: true');
    }

    public function index_get()
    {
        $_response = prep_response([
            'type' => 'unknown_method',
            'message' => 'Unknown method'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED, TRUE);

        $this->response($_response, $_response['code']);
    }

    public function index_post()
    {
        $_response = prep_response([
            'type' => 'unknown_method',
            'message' => 'Unknown method'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED, TRUE);

        $this->response($_response, $_response['code']);
    }

    public function index_put()
    {
        $_response = prep_response([
            'type' => 'unknown_method',
            'message' => 'Unknown method'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED, TRUE);

        $this->response($_response, $_response['code']);
    }

    public function index_delete()
    {
        $_response = prep_response([
            'type' => 'unknown_method',
            'message' => 'Unknown method'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED, TRUE);

        $this->response($_response, $_response['code']);
    }

    public function index_patch()
    {
        $_response = prep_response([
            'type' => 'unknown_method',
            'message' => 'Unknown method'
        ], REST_Controller::HTTP_METHOD_NOT_ALLOWED, TRUE);

        $this->response($_response, $_response['code']);
    }

    public function get_limit()
    {
        $limit = REST_Controller::get('limit', TRUE) ? REST_Controller::get('limit', TRUE) : self::DEFAULT_LIMIT;
        
        if (preg_match('/^[0-9]+$/i', $limit))
        {
            return $limit;
        }

        return self::DEFAULT_LIMIT;
    }

    public function get_offset()
    {
        $offset = REST_Controller::get('offset', TRUE);
        
        if (preg_match('/^[0-9]+$/i', $offset))
        {
            return $offset;
        }

        return NULL;
    }

    /**
     * @return void
     */
    protected function _prepare_jwt_auth()
    {
        // If whitelist is enabled it has the first chance to kick them out
        if ($this->config->item('rest_ip_whitelist_enabled'))
        {
            $this->_check_whitelist_auth();
        }

        $this->config->load('aho_config', TRUE);

        $http_auth = $this->input->server('HTTP_AUTHENTICATION') ?: $this->input->server('HTTP_AUTHORIZATION');
        $jwt_key = $this->config->item('jwt_key', 'aho_config');
        $jwt_algo = $this->config->item('jwt_algo', 'aho_config');
        list($jwt) = sscanf($this->input->server('HTTP_AUTHORIZATION'), 'Bearer %s');

        if ($jwt)
        {
            $this->load->model('aho_auth_model', 'aho_auth');
            $verify = $this->aho_auth->login_verify($jwt);
            if ($verify === FALSE)
            {
                $_response = prep_response([
                    'type' => 'unauthorized',
                    'message' => $this->aho_auth->message_string()
                ], REST_Controller::HTTP_UNAUTHORIZED, TRUE);

                $this->response($_response, $_response['code']);
            }
        }
        else
        {
            $_response = prep_response([
                'type' => 'bad_request',
                'message' => 'Bad request'
            ], REST_Controller::HTTP_BAD_REQUEST, TRUE);

            $this->response($_response, $_response['code']);
        }
    }

    protected function _check_token()
    {
        $this->load->model('aho_auth_model', 'aho_auth');
        $verify = $this->aho_auth->login_verify(get_cookie('token', TRUE));

        if ($verify === FALSE)
        {
            $_response = prep_response([
                'type' => 'unauthorized',
                'message' => $this->aho_auth->message_string()
            ], REST_Controller::HTTP_UNAUTHORIZED, TRUE);
            $this->response($_response, $_response['code']);
        }
    }
}
