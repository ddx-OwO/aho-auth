<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/REST_Controller.php');
class V1 extends REST_Controller {

	public function __construct($config = 'rest')
	{
		parent::__construct($config = 'rest');
		//$this->load->database();
		$this->load->library(['smartc_auth']);
        //$this->lang->load('smartc_auth');
    }

	public function auth_post()
	{
        $this->response([
            'status' => 'ok',
            'code' => 200,
            'result' => [
                $this->post(),
            ]
        ]);
	}

    public function login_post()
    {
        /*$data = [
            'status' => 'success',
            'code' => REST_Controller::HTTP_OK,
            'messages' => array(),
            'data' => [
                'site_content' => [
                    'login_title' => 'Halaman Login',
                    'username_label' => lang('username_label'),
                    'password_label' => lang('password_label'),
                    'login_button' => lang('login_label'),
                    'rememberme_label' => lang('rememberme_label')
                ]
            ]
        ];
        $this->response($data, $data['code']);*/
        $data = ['post' => $this->input->post('ddd'), 'server' => $_SERVER];
        $this->response($data, 200);
    }

    public function template_get($partial = '')
    {
        $lang_type = $this->input->get('lang') ? $this->input->get('lang') : 'indonesian';
        $lang_data = $this->lang->load('auth_page', $lang_type, TRUE);
        $data = [    
            'status' => 'success',
            'code' => REST_Controller::HTTP_OK,
            'messages' => '',
            'links' => [
                'rel' => 'self',
                'href' => $this->uri->uri_string()
            ]
        ];
        
        switch ($partial) {
            case 'login':
                $lang_value = $lang_data['login_page'];
                $data['data'] = [
                    'site_content' => [
                        'title' => 'Halaman Login',
                        'identity_label' => $lang_value['identity_label'],
                        'password_label' => $lang_value['password_label'],
                        'rememberme_label' => $lang_value['rememberme_label'],
                        'submit_button_label' => $lang_value['submit_button_label']
                    ]
                ];
                break;
            
            default:
                $data = [
                    'status' => 'error',
                    'code' => REST_Controller::HTTP_NOT_FOUND,
                    'messages' => 'The requested resource could not be found',
                    'data' => []
                ];
                break;
        }
        $this->response($data, $data['code']);
    }

	public function test()
	{
		$this->load->view('welcome_message');
	}
}
