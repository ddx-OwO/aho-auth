<?php
defined('BASEPATH') or exit('No direct access script allowed');

class Auth_test extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('smartc_auth'));
		$this->load->helper('form');
		$this->cookies = $this->config->item('cookie', 'smartc_auth_config');
	}

	public function index()
	{
		if($this->smartc_auth->is_logged_in() === FALSE)
		{
			redirect('auth/logout');
		}

		$output['users'] = $this->smartc_auth->users()->result();
		foreach ($output['users'] as $key => $value) 
		{
			$output['users'][$key]->groups = $this->smartc_auth->user_groups($value->user_id)->result();
		}
		
        echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	public function login()
	{
		$data = $this->input->post('field');
		$rememberme = (bool)$this->input->post('rememberme');
		if($data)
		{
			$login = $this->smartc_auth->login($data['username'], $data['password'], $rememberme);
			if($login)
			{
				redirect('auth');
			}
			else
			{
				$this->session->set_flashdata('messages', $this->smartc_auth->messages());
			}
		}
		else
		{
			if($this->smartc_auth->is_logged_in() === TRUE)
			{
				redirect('auth');
			}
		}

        $output = array();
        var_dump(base_convert(bin2hex(openssl_random_pseudo_bytes(32)), 16, 36));
        var_dump($this->smartc_auth->get_user_id('admin'));

        // Login page data for tempalate parser
        /*$login_page_data = lang('login_page');
        $login_page_data['logo'] = img_path('smart-c.png', 'template', 'stile');
        $output['login_page'] = array($login_page_data);
		$this->parser->parse('stile/login', $output);*/
	}

    public function login_ajax()
    {
        $output = array();
        if ($this->input->is_ajax_request() === TRUE)
        {
            $data = $this->input->post('field');
            $rememberme = (bool)$this->input->post('rememberme');
            if($data)
            {
                $login = $this->smartc_auth->login($data['username'], $data['password'], $rememberme);
                if($login)
                {
                    $output = array(
                        'status' => 'success',
                        'code' => 200,
                        'messages' => array('Login Success'),
                        'data' => array(
                            'redirect_to' => base_url('auth'),
                        )
                    );
                }
                else
                {
                    $output = array(
                        'status' => 'success',
                        'code' => 403,
                        'messages' => array('Error: Login Failed'),
                        'data' => array()
                    );
                }

                $this->output
                     ->set_status_header($output['code'])
                     ->set_content_type('application/json')
                     ->set_output(json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                exit();
            }

            $output = array(
                'status' => 'error',
                'code' => 403,
                'messages' => array('Error: No direct access allowed'),
                'data' => array()
            );
        }

        $output = array(
            'status' => 'error',
            'code' => 200,
            'messages' => array(),
            'data' => array(
                'site_content' => array(
                    'login_title' => 'Halaman Login',
                    'logo' => img_path('smart-c.png', 'template', 'stile'),
                    'username_label' => lang('username_label'),
                    'password_label' => lang('password_label'),
                    'login_button' => lang('login_label'),
                    'rememberme_label' => lang('rememberme_label')
                )
            )
        );
        $this->output
             ->set_status_header($output['code'])
             ->set_content_type('application/json')
             ->set_output(json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

	public function register()
	{
		$validations = array(
			array(
				'field' => 'field[]',
				'label' => 'Form',
				'rules' => 'required|trim'
			),
			array(
				'field' => 'field[username]',
				'label' => 'Username',
				'rules' => array(
					'required',
					array('username_check', array($this->smartc_auth, 'username_check'))
				)
			),
			array(
				'field' => 'field[email]',
				'label' => 'Email',
				'rules' => 'valid_email'
			)
		);
		$this->form_validation->set_rules($validations);

		$data = $this->input->post('field');
		if ($this->form_validation->run() === TRUE)
		{
			$this->smartc_auth->register($data['username'], $data['password'], $data['email'], '', '', array(3));
			$this->session->set_flashdata('messages', $this->smartc_auth->messages());
		}
		$this->load->view('smartc_auth/register');
	}

	public function activation()
	{
		$identity = $this->input->get('identity');
		$code = $this->input->get('code');
		if($this->smartc_auth->activate($identity, $code))
		{
			echo $this->smartc_auth->messages();
		}
		else
		{
			die('Forbidden');
		}
	}

	public function logout()
	{
		$this->smartc_auth->logout();
		redirect('auth/login');
	}
}