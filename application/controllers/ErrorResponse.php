<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/Smartc_API.php');

class ErrorResponse extends Smartc_API {

	public function __construct()
	{
		parent::__construct();
	}

    public function index_get()
    {
        $res = $this->set_http_code(REST_Controller::HTTP_METHOD_NOT_ALLOWED)
                    ->prepare_error_response('unknown_method', $this->lang->line('text_rest_unknown_method'));
                    
        $this->response($res, $res['code']);
    }
}
