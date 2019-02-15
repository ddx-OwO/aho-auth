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

        $this->load->model('aho_user_model', 'aho_user');
        $this->identity_column = $this->config->item('identity_column', 'aho_config');
        $this->_prepare_jwt_auth();
    }

    public function index_get($identity = NULL)
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

            $this->db->select($valid_fields);
        }
        else
        {
            $this->db->select('user_id,username,email,'.$this->identity_column);
        }

        if (isset($identity))
        {
            $data = $this->aho_user
                         ->user($identity)
                         ->row();

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
            $this->db->limit($limit, $offset);
            $data = $this->aho_user
                         ->users()
                         ->result();
            $_response = prep_response($data, REST_Controller::HTTP_OK, $extra);
        }

        $this->set_response($_response, $_response['code']);
    }

    public function index_post()
    {
        $this->load->library('form_validation');
        $identity = $this->post($this->identity_column, TRUE);
        $password = $this->post('password');
        $email = $this->post('email');
        // $extra_data = $this->post('extra_data');
        $groups = $this->post('groups');
        $extra_data = array(
            'fullname' => $this->post('fullname', TRUE),
            'identity_number' => $this->post('identity_number', TRUE),
            'address' => $this->post('identity_number', TRUE),
            'gender' => $this->post('gender', TRUE),
            'phone' => $this->post('phone', TRUE),
            'birthday' => $this->post('birthday', TRUE)
        )
        $_response = '';

        $validations = array(
            array(
                'field' => 'username',
                'label' => 'Username',
                'rules' => array(
                    'required',
                    array('username_check', array($this->aho_user, 'username_check'))
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
            if ($this->aho_user->register($identity, $password, $email, $extra_data, $groups))
            {           
                $_response = prep_response([
                    'message' => $this->message->message_string()
                ], REST_Controller::HTTP_CREATED);
            }
            else
            {
                // Something went wrong here. 
                // Unpredictable bugs feature.
                $_response = prep_response([
                    'message' => $this->message->message_string()
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR, TRUE);

                $this->response($_response, $_response['code']);
            }
        }
        else
        {
            $_response = prep_response([
                'type' => 'bad_request',
                'message' => $this->form_validation->error_array(NULL, NULL)
            ], REST_Controller::HTTP_BAD_REQUEST, TRUE);
        }

        $this->response($_response, $_response['code']);
    }

    public function index_delete()
    {
        $identity = $this->delete('identity', TRUE);
        $delete = $this->aho_user->user_delete($identity);
        $_response = '';

        if ($delete)
        {
            $_response = prep_response([
                'message' => $this->message->message()
            ], REST_Controller::HTTP_OK);
        }
        else
        {
            $_response = prep_response([
                'type' => 'bad_request',
                'message' => $this->message->message()
            ], REST_Controller::HTTP_BAD_REQUEST, TRUE);
        }

        $this->response($_response, $_response['code']);
    }

    private function _validate_fields($table, $fields, $filter_sensitive_data = TRUE)
    {
        $db_columns = $this->db->list_fields($this->tables['users']);
        $fields = explode(',', $fields);
        $valid_fields = array();

        // We need to validate the fields and hide the sensitive data
        foreach ($db_columns as $column) 
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
    }
}
