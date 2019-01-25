<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends REST_Controller {

    const DEFAULT_LIMIT = 50;

    public function __construct($config = 'rest')
    {
        parent::__construct($config = 'rest');

        header('Access-Control-Allow-Credentials: true');
    }

    public function index_get()
    {
        $_response = prep_response([
            'type' => 'bad_request',
            'message' => 'Bad request'
        ], REST_Controller::HTTP_BAD_REQUEST, TRUE);

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
    protected function _check_session()
    {
        if ($this->smartc_auth->is_logged_in() === FALSE)
        {
            $res = $this->set_http_code(REST_Controller::HTTP_UNAUTHORIZED)
                        ->prepare_error_response('unauthorized', 'Invalid Login');

            REST_Controller::response($res, $res['code']);
        }
    }

    protected function _check_authority($level)
    {
        
    }
}
