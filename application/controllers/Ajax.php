<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/REST_Controller.php');

class Ajax extends REST_Controller {

    private $_responses = [
        'status',
        'data',
        'errors',
        'links',
        'csrf'
    ];

	public function __construct($config = 'ajax')
	{
		parent::__construct($config = 'ajax');

		$this->load->library('smartc_auth');
        
	}

    public function users_get($user_id = '')
    {
        //if ( ! $this->smartc_auth->is_logged_in())

        $res = '';
        $user_id = (int) $user_id;

        $limit = $this->input->get('limit', TRUE);
        $offset = $this->input->get('offset', TRUE);

        if ( ! empty($user_id))
        {
            $res = $this->smartc_auth->user_id($user_id)->row();

            if (is_null($res))
            {
                $this->_set_responses([], REST_Controller::HTTP_REQUEST_TIMEOUT, 'Data not found');
            }

            $this->_set_responses($res, REST_Controller::HTTP_OK);
        }

        if (isset($limit)) $this->smartc_auth->limit($limit);
        if (isset($offset)) $this->smartc_auth->offset($offset);

        $res = $this->smartc_auth->users()->result();

        $this->_set_responses($res, REST_Controller::HTTP_OK);
    }

	public function auth_post()
	{
        $data = $this->input->post(NULL, TRUE);

    	$identity = $data['username'];
    	$password = $data['password'];
    	$rememberme = isset($data['rememberme']) ? TRUE : FALSE;

    	if ($rememberme)
    	{
    		$this->_set_responses($this->smartc_auth->user('admin')->row(), REST_Controller::HTTP_OK);
    	}
    	else
    	{
            $this->_set_responses(['wkwk' => 'lel'], REST_Controller::HTTP_UNAUTHORIZED, 'lol', 'lalalala');
    	}
	}

    private function _set_responses($data, $http_code = 200, $errors = NULL, $continue = FALSE)
    {
        if (array_key_exists($http_code, $this->http_status_codes))
        {
            $status = $this->http_status_codes[$http_code];
        }
        else
        {
            $status = $http_code;
        }

        $this->_response['status'] = $status;
        $this->_response['data'] = $data;
        $this->_response['csrf'] = $this->_generate_csrf();

        if ($this->config->item('csrf_protection') === FALSE)
        {
            unset($this->_response['csrf']);
        }

        if (isset($errors) && ! empty($errors))
        {
            $this->_response['errors'] = $errors;
        }
        else
        {
            unset($this->_response['errors']);
        }

        return $this->response($this->_response, $http_code, $continue);
    }

    /*private function _response($http_code = 200, $continue = FALSE)
    {
        $response = array();

        if (isset($this->_response) && ! empty($this->_response))
        {
            foreach ($this->_response['data'] as $data) 
            {
                $response[] = $data;
            }

            // Reset the variable
            $this->_response = array();
        }

        //$_response = $this->_response;

        //$this->_response = array();

        return $this->response($response, $http_code, $continue);
    }

    private function _response_data($data, $errors = NULL)
    {
        $this->_response['data'][] = $data;
        $this->_response['csrf'] = $this->_generate_csrf();

        if ( ! empty($errors))
        {
            $this->_response['errors'][] = $errors;
        }

        return $this;
        //$this->_response_with_csrf($_response, $http_code, $continue);
    }*/

    private function _generate_csrf()
    {
        $csrf = [
            'csrf_token' => $this->security->get_csrf_hash(),
            'csrf_token_name' => $this->security->get_csrf_token_name()
        ];

        return $csrf;
    }

	public function csrf()
	{
		$this->_response($this->_generate_csrf());
	}

	public function template($page = '')
	{
		$response = [
			'status' => 'success',
	        'code' => 200,
	        'messages' => array(),
	        'links' => [
	            'rel' => 'self',
	            'href' => $this->uri->uri_string()
	        ]
	    ];

	    $lang = $this->input->cookie('lang');

	    if ($lang === NULL)
	    {
	    	$lang = 'indonesian';
	    	set_cookie('lang', $lang, YEAR_IN_SECONDS);
	    }

	    $this->lang->load('auth_page', $lang);

		switch ($page) {

			case 'login':
			case 'register':

				$this->load->view('stile/login_component', lang('auth_page'));

				break;

			case 'test':
				
				var_dump(strpos('http://localhost:4200/login', 'https://localhost/'));
				break;
			
			default:
				$response = [
					'status' => 'error',
			        'code' => 404,
			        'messages' => ['Template view is not found'],
			        'links' => [
			            'rel' => 'self',
			            'href' => $this->uri->uri_string()
			        ]
			    ];
				break;
		}

		//$this->_response($response, $response['code']);
	}

	/*private function _response($data, $http_code = 200, $extra = array())
	{
        if ( ! is_array($extra))
        {
            return FALSE;
        }

        $_response = [
            'code' => $http_code,
            'data' => $data,
        ];

        if ( ! empty($extra))
        {
            $_response = array_merge($_response, $extra);
        }
        
		$this->output->set_status_header($http_code)
			         ->set_content_type('application/json', 'utf-8')
			         ->set_output(json_encode($_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}

    public function login_get()
    {
        $ch = curl_init('https://localhost/api/auth');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'hasemeleh');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'admin:1234');
        curl_setopt($ch, CURLOPT_REFERER, 'lel');
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['ddd' => 'dwdw']);
        $result = curl_exec($ch);

        if ($result === FALSE)
        {
            echo curl_error($ch);
        }
        curl_close($ch);

        echo $result;
    }

    */
}
