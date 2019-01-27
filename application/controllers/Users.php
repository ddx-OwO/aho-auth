<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    private $identity_column;

    private $sensitive_data = [
        'password',
        'activation_code',
        'forgot_password_code'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('aho_auth_model', 'aho_auth');
        $this->identity_column = $this->aho_auth->identity_column;
        $this->_check_token();
    }

    public function index_get($identity = NULL)
    {
        return $this->response(['ani' => 'budi']);
    }

    /*public function index_get($identity = NULL)
    {
        $_response = '';
        $fields = $this->get('fields', TRUE);
        $limit = $this->get_limit();
        $offset = $this->get_offset();
        $extra = [
            'links' => [
                [
                    'rel' => 'self',
                    'href' => $this->uri->uri_string(),
                ],
            ]
        ];

        if (isset($fields))
        {
            $valid_fields = $this->_validate_fields($this->tables['users'], $fields, TRUE);

            if (empty($valid_fields))
            {
                $_response = prep_response([
                    'type' => 'bad_request',
                    'message' => 'Bad request'
                ], REST_Controller::HTTP_BAD_REQUEST, TRUE);

                $this->response($_response, $_response['code']);
            }

            $valid_fields = implode(',', $valid_fields);

            $this->smartc_auth->select($valid_fields);
        }
        else
        {
            $this->smartc_auth->select('user_id,username,user_email,'.$this->identity_column);
        }

        if (isset($identity))
        {
            $data = $this->smartc_auth->user($identity)->row();

            if (empty($data))
            {
                $_response = prep_response([
                    'type' => 'not_found',
                    'message' => 'User not found'
                ], REST_Controller::HTTP_NOT_FOUND, TRUE);
            }
            else
            {
                $_response = prep_response($data, REST_Controller::HTTP_OK, $extra);
            }
        }
        else
        {
            $data = $this->smartc_auth->limit($limit)
                                      ->offset($offset)
                                      ->users()
                                      ->result();

            $_response = prep_response($data, REST_Controller::HTTP_OK, $extra);
        }

        $this->set_response($_response, $_response['code']);
    }

    /*public function index_post()
    {
        $identity = $this->post($this->identity_column, TRUE);
        $password = $this->post('password');
        $email = $this->post('email');
        $extra_data = $this->post('extra_data');
        $groups = $this->post('groups');
        $_response = '';

        $validations = array(
            array(
                'field' => $this->identity_column,
                'label' => $this->identity_column,
                'rules' => array(
                    'required',
                    array('username_check', array($this->smartc_auth, 'username_check'))
                )
            ),
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'required|trim|valid_email'
            ),
            array(
                'field' => 'password',
                'label' => $this->lang->line('password_label'),
                'rules' => 'required'
            )
        );
        $this->form_validation->set_rules($validations);

        if ($this->form_validation->run() === TRUE)
        {
            if ($this->smartc_auth->register($identity, $password, $email, $extra_data, $groups))
            {           
                $_response = prep_response([
                    'message' => $this->smartc_auth->message()
                ], REST_Controller::HTTP_CREATED);
            }
            else
            {
                // Something wrong here, i'll fix it later '-')b
                $_response = prep_response([
                    'message' => $this->smartc_auth->message()
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR, TRUE);

                $this->response($_response, $_response['code']);
            }
        }
        else
        {
            $_response = prep_response([
                'type' => 'bad_request',
                'message' => $this->form_validation->error_string(NULL, NULL)
            ], REST_Controller::HTTP_BAD_REQUEST, TRUE);
        }

        $this->response($_response, $_response['code']);
    }

    public function index_delete()
    {
        $identity = $this->delete('identity', TRUE);
        $delete = $this->smartc_auth->user_delete($identity);
        $_response = '';

        if ($delete)
        {
            $_response = prep_response([
                'message' => $this->smartc_auth->message()
            ], REST_Controller::HTTP_OK);
        }
        else
        {
            $_response = prep_response([
                'type' => 'bad_request',
                'message' => $this->smartc_auth->message()
            ], REST_Controller::HTTP_BAD_REQUEST, TRUE);
        }

        $this->response($_response, $_response['code']);
    }

    private function _validate_fields($table, $fields, $filter_sensitive_data = TRUE)
    {
        $valid_columns = $this->db->list_fields($this->tables['users']);
        $fields = explode(',', $fields);
        $valid_fields = array();

        // We need to validate the fields and hide the sensitive data
        foreach ($valid_columns as $column) 
        {
            if (in_array($column, $fields))
            {
                if ($filter_sensitive_data)
                {
                    if ( ! in_array($column, $this->sensitive_data))
                    {
                        $valid_fields[] = $column;
                    }
                }
                else
                {
                    $valid_fields[] = $column;
                }
            }
        }

        return $valid_fields;
    }*/
}
