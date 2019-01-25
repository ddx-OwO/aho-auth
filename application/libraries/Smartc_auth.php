<?php
/**
 * Name:    Smartc Auth
 * Author:  Dewa Andhika Putra
 *          dewaandhika18@gmail.com
 *          @dwzzzl
 *
 * Created:  12.01.2018
 *
 * Description:  Smartc Auth adalah sebuah library authentication yang terinspirasi dari library Ion Auth dengan
 * beberapa penambahan fitur.
 *
 * Special Thanks to: Ben Edmunds
 *
 * Requirements: PHP5 or above
 *
 * @package    Smartc-Auth-CodeIgniter
 * @author     Dewa Andhika Putra
 * @link       http://github.com/dwzzzl/Smartc-Auth-CodeIgniter
 * @version    0.1.0
 */

defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;

class Smartc_auth
{
	/**
     * Time constants
     */
    const MINUTE_IN_SECONDS = 60;
    const HOUR_IN_SECONDS = 60 * MINUTE_IN_SECONDS;
    const DAY_IN_SECONDS = 24 * HOUR_IN_SECONDS;
    const WEEK_IN_SECONDS = 7 * DAY_IN_SECONDS;
    const MONTH_IN_SECONDS = 30 * DAY_IN_SECONDS;
    const YEAR_IN_SECONDS = 365 * DAY_IN_SECONDS;
    
    /**
     * Tables
     * 
     * @var array
     */
    public $tables = array();

   	/**
	 * Cookies name
	 *
	 * @var	array
	 */
	public $cookies = array();

    /**
     * User identity column
     * 
     * @var string
     */
    public $identity_column;

    /**
     * Activation code
     * 
     * @var string
     */
    public $activation_code;

    /**
     * User forgot password code
     * 
     * @var string
     */
    public $forgot_password_code;

    /**
     * Set
     * 
     * @var array
     */
    public $_auth_set = array();

    /**
     * Where
     * 
     * @var array
     */
    public $_auth_where = array();

    /**
     * Where in
     * 
     * @var array
     */
    public $_auth_where_in = array();

    /**
     * Select
     * 
     * @var array
     */
    public $_auth_select = array();

    /**
     * Limit
     * 
     * @var string
     */
    public $_auth_limit = NULL;

    /**
     * Offset
     * 
     * @var string
     */
    public $_auth_offset = NULL;

    /**
     * Order by
     * 
     * @var string
     */
    public $_auth_order_by = NULL;

    /**
     * Order
     * 
     * @var string
     */
    public $_auth_order = NULL;

    /**
     * Group by
     * 
     * @var array
     */
    public $_auth_group_by = array();

    /**
     * Response
     * 
     * @var string
     */
    protected $response = NULL;

    /**
     * Messages array
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Message start delimeter
     *
     * @var string
     */
    protected $message_start_delimeter;

    /**
     * Message end delimeter
     *
     * @var string
     */
    protected $message_end_delimeter;

    /**
     * New line tag for each messages
     *
     * @var string
     */
    protected $message_new_line;

	/**
	 * Allowed characters for username
	 *
	 * @var	string
	 */
	protected $allowed_chars;

	/**
	 * Username maximum length
	 *
	 * @var	int
	 */
	protected $username_max_length;

	/**
	 * Username minimum length
	 *
	 * @var	int
	 */
	protected $username_min_length;

	/**
	 * Salt algorithm type constant
	 *
	 * @var	constant
	 */
	private $salt_type;

	/**
     * Array of protected User IDs
     *
     * @var array
     */
    private $protected_users = array();

	public function __construct($config = 'smartc_auth_config')
	{
		$this->config->load($config, TRUE);

        $this->load->database();

		// Get the language
        $language = $this->config->item('auth_language', 'smartc_auth_config');
        if ($language === NULL)
        {
            $language = 'indonesian';
        }

        // Load the language file
        $this->lang->load('smartc_auth', $language, FALSE, TRUE, __DIR__.'/../');

        $this->load->helper(array('cookie', 'url', 'language', 'string', 'email', 'date'));

        // Initialize objects
        $this->tables = $this->config->item('tables', 'smartc_auth_config');
        $this->protected_users = $this->config->item('protected_users', 'smartc_auth_config');
        $this->identity_column = $this->config->item('identity_column', 'smartc_auth_config');
        $this->join = $this->config->item('join', 'smartc_auth_config');

        $this->message_start_delimeter = $this->config->item('message_start_delimeter', 'smartc_auth_config');
        $this->message_end_delimeter = $this->config->item('message_end_delimeter', 'smartc_auth_config');
        $this->message_new_line = $this->config->item('message_new_line', 'smartc_auth_config');

        // Initialize response for static return
        $this->response = new stdClass();
		
		$this->allowed_chars = $this->config->item('username_allowed_chars', 'smartc_auth_config');
		$this->username_max_length = $this->config->item('username_max_length', 'smartc_auth_config');
		$this->username_min_length = $this->config->item('username_min_length', 'smartc_auth_config');

		$this->cookies = $this->config->item('cookies', 'smartc_auth_config');
		$this->salt_type = $this->config->item('salt_type', 'smartc_auth_config');

		$this->load->library(array('form_validation', 'email', 'session', 'user_agent'));
		//$this->load->model('smartc_auth_model');

		log_message('debug', 'Smartc Auth: Library loaded');
	}

	public function __call($method, $arguments)
	{
		if (!method_exists( $this->smartc_auth_model, $method) )
		{
			throw new Exception('Undefined method Smartc_auth::' . $method . '() called');
		}
		return call_user_func_array( array($this->smartc_auth_model, $method), $arguments);
	}

	/**
	 * __get
	 *
	 * Enables the use of CI super-global without having to define an extra variable.
	 *
	 * I can't remember where I first saw this, so thank you if you are the original author. -Militis
	 *
	 * @param    string $var
	 *
	 * @return    mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

	 /**
     * @param string $select
     * @return static
     */
    public function select($select)
    {
        $this->_auth_select[] = $select;
        return $this;
    }

    /**
     * @param array|string $where 
     * @param null|string $value 
     * @return static
     */
    public function where($where, $value = NULL)
    {
        if( ! is_array($where))
        {
            $where = array($where => $value);
        }
        array_push($this->_auth_where, $where);
        return $this;
    }

    /**
     * @param string $where 
     * @param array $value 
     * @return static
     */
    public function where_in($where, $value = array())
    {
        $this->_auth_where_in[] = array('key' => $where, 'value' => $value);
        return $this;
    }

    /**
     * @param array|string $column 
     * @param null|string $value 
     * @return static
     */
    public function set($column, $value = NULL)
    {
        if( ! is_array($column))
        {
            $column = array($column => $value);
        }
        array_push($this->_auth_set, $column);
        return $this;
    }

    /**
     * @param int $limit 
     * @return static
     */
    public function limit($limit)
    {
        $this->_auth_limit = $limit;
        return $this;
    }

    /**
     * @param int $offset 
     * @return static
     */
    public function offset($offset)
    {
        $this->_auth_offset = $offset;
        return $this;
    }

    /**
     * @param string $group_by
     * @return static
     */
    public function group_by($group_by)
    {
        $this->_auth_group_by[] = $group_by;
        return $this;
    }

    /**
     * @param string $column 
     * @param string $order 
     * @return static
     */
    public function order_by($column, $order = 'desc')
    {
        $this->_auth_order_by = $column;
        $this->_auth_order = $order;
        return $this;
    }

    /**
     * @return object|mixed
     */
    public function row()
    {
        return $this->response->row();
    }

    /**
     * @return array|mixed
     */
    public function row_array()
    {
        return $this->response->row_array();
    }

    /**
     * @return mixed
     */
    public function result()
    {
        return $this->response->result();
    }

    /**
     * @return array|mixed
     */
    public function result_array()
    {
        return $this->response->result_array();
    }

    /**
     * @return void
     */
    public function free_result()
    {
        $this->response->free_result();
    }

    /**
     * Get
     * @param string $table 
     * @param int|null $limit 
     * @param int|null $offset 
     * 
     * @return static
     */
    public function get($table, $limit = NULL, $offset = NULL)
    {
        if (isset($this->_auth_select) && ! empty($this->_auth_select))
        {
            foreach ($this->_auth_select as $select) 
            {
                $this->db->select($select);
            }

            // Reset the variable
            $this->_auth_select = array();
        }
        else
        {
            // default selects
            $this->db->select("{$table}.*");
        }

        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        if ( ! empty($this->_auth_group_by))
        {
            $this->db->group_by($group_by);
            $this->_auth_group_by = array();
        }

        if (isset($limit))
        {
            $this->_auth_limit = $limit;
        }

        if (isset($offset))
        {
            $this->_auth_offset = $offset;
        }

        if (isset($this->_auth_limit) && isset($this->_auth_offset))
        {
            $this->db->limit($this->_auth_limit);
            $this->db->offset($this->_auth_offset);

            $this->_auth_limit  = NULL;
            $this->_auth_offset = NULL;
        }
        elseif (isset($this->_auth_limit)) 
        {
            $this->db->limit($this->_auth_limit);

            $this->_auth_limit = NULL;
        }

        if (isset($this->_auth_order_by) && isset($this->_auth_order))
        {
            $this->db->order_by($this->_auth_order_by, $this->_auth_order);

            $this->_auth_order    = NULL;
            $this->_auth_order_by = NULL;
        }

        $this->response = $this->db->get($table);
        return $this;
    }

    /**
     * Add bulk data
     * 
     * @param string $table 
     * @param array $data 
     * 
     * @return int|bool Return the affected rows or FALSE on failure
     */
    public function add_bulk($table, $data = array())
    {
        // Initialize batch variable
        $batch = array();

        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $set_data) 
            {
                $batch[] = $set_data;
            }
            $this->_auth_set = array();
        }

        // If there is value in $data, we use it
        if ( ! empty($data))
        {
            $batch = $data;
        }

        $this->db->trans_begin();

        $affected_rows = $this->db->insert_batch($table, $batch);

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        unset($batch);

        return $affected_rows;
    }

    /**
     * Add
     * 
     * @param string $table 
     * @param array  $data
     * @return int|bool
     */
    public function add($table, $data = NULL)
    {
        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $set_data) 
            {
                $this->db->set($set_data);
            }
            $this->_auth_set = array();
        }

        if ( ! empty($data)) 
        {
            $this->db->set($data);
        }

        // Begin the transaction
        $this->db->trans_begin();

        $this->db->insert($table);

        $id = $this->db->insert_id();

        // If there is no insert_id, then the return value will be affected_rows()
        if($id === 0)
        {
            $id = $this->db->affected_rows();
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return $id;
    }

    /**
     * update
     * 
     * @param string $table 
     * @return bool
     */
    public function update($table)
    {
        if (isset($this->_auth_set) && ! empty($this->_auth_set))
        {
            foreach ($this->_auth_set as $data) 
            {
                $this->db->set($data);
            }
            $this->_auth_set = array();
        }

        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        $this->db->trans_begin();

        $this->db->update($table);

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    /**
     * Delete
     * 
     * @param string $table 
     * @return bool
     */
    public function delete($table)
    {
        if (isset($this->_auth_where) && ! empty($this->_auth_where))
        {
            foreach ($this->_auth_where as $where) 
            {
                $this->db->where($where);
            }
            $this->_auth_where = array();
        }

        if (isset($this->_auth_where_in) && ! empty($this->_auth_where_in))
        {
            foreach ($this->_auth_where_in as $where) 
            {
                $this->db->where_in($where['key'], $where['value']);
            }
            $this->_auth_where_in = array();
        }

        $this->db->trans_begin();

        $this->db->delete($table);

        if($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    /**
     * Get users
     * 
     * @param array $groups
     * @return static
     */
    public function users($groups = array())
    {
        if(isset($groups) && ! empty($groups))
        {
            $this->db->distinct();
            $this->db->join($this->tables['user_groups'], 
                "{$this->tables['user_groups']}.{$this->join['users']} = {$this->tables['users']}.user_id",
                'inner'
            );
            $this->db->where_in("{$this->tables['user_groups']}.{$this->join['groups']}", $groups);
        }

        return $this->get("{$this->tables['users']}");
    }

    /**
     * Is user exist
     * 
     * @param string $identity
     * @return bool
     */
    public function is_user_exist($identity)
    {
        $query = $this->db->where([$this->identity_column => $identity])
                          ->limit(1)
                          ->get($this->tables['users']);

        return $query->num_rows() > 0;
    }

    /**
     * Username check
     * 
     * @param string $username
     * @return bool
     */
    /*public function username_check($username)
    {
        return $this->db->where('username', $username)
                        ->limit(1)
                        ->count_all_results($this->tables['users']) > 0;
    }*/

    /**
     * Username availability check. This method also can be used in Form_validation callback
     * 
     * @param string $username 
     * @return bool
     */
    public function username_check($username)
    {
        $username = strtolower($username);
        $match = (bool)preg_match('/^['.$this->allowed_chars.']+$/i', $username);
        $length = strlen($username);

        // Check username allowed chars
        if($match === FALSE )
        {
            $this->set_message(
                sprintf(
                    lang('account_create_invalid_identity'), 
                    lang('username_label')
                )
            );
            $this->form_validation->set_message('username_check', lang('account_create_invalid_identity'));
            return FALSE;
        }
        else if ($length < $this->username_min_length && $this->username_min_length !== 0)
        {
            //Check username minimum length
            $this->set_message(
                sprintf(
                    lang('account_create_min_length'), 
                    lang('username_label'), 
                    $this->username_min_length
                )
            );
            $this->form_validation->set_message('username_check', sprintf(lang('account_create_min_length'), lang('username_label'), $this->username_min_length));
            return FALSE;
        }
        else if ($length > $this->username_max_length && $this->username_max_length !== 0)
        {
            //Check username maximum length
            $this->set_message(
                sprintf(
                    lang('account_create_max_length'), 
                    lang('username_label'), 
                    $this->username_max_length
                )
            );
            $this->form_validation->set_message('username_check', sprintf(lang('account_create_max_length'), lang('username_label'), $this->username_max_length));
            return FALSE;
        }

        // Check existed username
        if($this->smartc_auth_model->username_check($username) === TRUE)
        {
            $this->set_message(
                sprintf(
                    $this->lang->line('account_create_duplicate_identity'), 
                    $this->lang->line('username_label')
                )
            );
            $this->form_validation->set_message('username_check', lang('account_create_duplicate_identity'));
            return FALSE;
        }

        $this->set_message(
            sprintf(
                lang('account_create_available_identity'), 
                lang('username_label')
            )
        );
        $this->form_validation->set_message('username_check', lang('account_create_available_identity'));
        return TRUE;
    }

    /**
     * Email check
     * 
     * @param string $email
     * @return bool
     */
    public function email_check($email)
    {
        return $this->db->where('user_email', $email)
                        ->limit(1)
                        ->count_all_results($this->tables['users']) > 0;
    }

    /**
     * Get user by identity
     * 
     * @param string $identity 
     * @return static
     */
    public function user($identity)
    {
        $this->where(["{$this->tables['users']}.{$this->identity_column}" => $identity]);
        $this->limit(1);
        $this->order_by($this->identity_column, 'desc');
        $this->users();

        return $this;
    }

    /**
     * Get user by id
     * 
     * @param int $id 
     * @return static
     */
    public function user_id($id)
    {
        //$this->select('user_id');
        $this->where(["{$this->tables['users']}.user_id" => $id]);
        $this->limit(1);
        //$this->order_by("{$this->tables['users']}.user_id", 'desc');
        $this->users();

        return $this;
    }

    /**
     * User update
     * 
     * @param string $identity 
     * @param array $data 
     * @param array $groups 
     * 
     * @return bool
     */

    public function user_update($identity, $data = array(), $groups = array())
    {
        $update_data = $data;
        $user_data = $this->select('user_id,username,user_email,'.$this->identity_column)->user($identity)->row();

        if (array_key_exists('username', $update_data))
        {
            if($user_data->username != $update_data['username'])
            {
                if($this->username_check($update_data['username']) === FALSE)
                {
                    return FALSE;
                }
            }
        }
        
        if(array_key_exists('user_email', $update_data))
        {
            if($user_data->user_email != $update_data['user_email'])
            {
                if($this->smartc_auth_model->email_check($update_data['user_email']))
                {
                    $this->set_message(
                        sprintf(
                            $this->lang->line('account_create_duplicate_identity'), 
                            $this->lang->line('email_label')
                        )
                    );
                    return FALSE;
                }
            }
        }

        $update = $this->set($update_data)
                       ->where($this->identity_column, $identity)
                       ->update($this->tables['users']);
        
        if ($update)
        {
            if ( ! empty($groups))
            {
                if( ! is_array($groups))
                {
                    $groups = array($groups);
                }
                $this->smartc_auth_model->move_to_group($user_data->user_id, $groups);
            }
            $this->set_message('account_update_success');
            return TRUE;
        }
        else
        {
            $this->set_message('account_update_failed');
            return FALSE;
        }
    }

    /**
     * User delete
     * 
     * @param string $identity 
     * @return type
     */
    public function user_delete($identity)
    {
        if($this->is_user_exist($identity))
        {
            // Check if the user listed in protected users
            foreach ($this->protected_users as $user) 
            {
                if(strcmp($identity, strtolower($user)) === 0)
                {
                    $this->set_message('account_delete_protected');
                    return FALSE;
                }
            }

            $delete = $this->where($this->identity_column, $identity)->delete($this->tables['users']);
            
            if($delete)
            {
                $this->set_message('account_delete_success');
                return TRUE;
            }
            else
            {
                $this->set_message('account_delete_failed');
                return FALSE;
            }
        }
        else 
        {
            $this->set_message('account_error_unregistered');
            return FALSE;
        }
    }

    /**
     * Add user
     *
     * @param   string  $identity   User identity
     * @param   string  $password   User password. Automatically hashed with algo based on config
     * @param   string  $email      Email
     * @param   array   $extra      Additional data
     * @param   array   $groups     Array of Group ID
     * @return  mixed The user id or FALSE on failure
     */

    public function register($identity, $password, $email, $extra = NULL, $groups = NULL)
    {

        // Generate activation code hash
        $this->activation_code = hash('sha256', $this->security->get_random_bytes(128));

        $default_status = ($this->config->item('activation_method', 'smartc_auth_config') === FALSE) ? 1 : 0;

        // Handling the password length is truncated if longer than 72 characters
        // See: https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
        $hashed_password = password_hash(
            base64_encode(
                hash('sha256', $password, TRUE)
            ), 
            $this->salt_type
        );

        $data = array(
            $this->identity_column => $identity,
            'username' => $identity,
            'user_password' => $hashed_password,
            'user_email' => $email,
            'user_activation_code' => $this->activation_code,
            'user_status' => $default_status
        );

        // Merge the additional data
        if (isset($extra) && ! empty($extra))
        {
            $data = array_merge($data, $extra);
        }

        // Get the user id
        $id = $this->set($data)->add($this->tables['users']);

        if ($id !== FALSE)
        {
            if (isset($groups) && ! empty($groups))
            {
                foreach ($groups as $group_id) 
                {
                    $this->add_to_group($id, $group_id);
                }
            }
            else
            {
                $default_group = $this->config->item('user_default_group', 'smartc_auth_config');
                $group = $this->select('group_id')->where('group_name', $default_group)->groups()->row();

                $this->add_to_group($id, $group->group_id);

                unset($group, $default_group);
            }

            // Check if activation method is using email and user default status is 0 (Nonactive)
            // Email activation only works when user default status is 0 (Nonactive)
            $activation_method = $this->config->item('activation_method', 'smartc_auth_config');
            
            if ($activation_method === 'email')
            {
                $email_message_data = array(
                    'identity' => $identity,
                    'activation_code' => $this->activation_code
                );
                $email_message = $this->load->view($this->config->item('email_templates', 'smartc_auth_config').$this->config->item('email_activate', 'smartc_auth_config'), $data, TRUE);

                $this->email->clear();

                $this->email->from($this->config->item('admin_email', 'smartc_auth_config'), $this->config->item('email_subject', 'smartc_auth_config'));
                $this->email->to($email);
                $this->email->subject($this->config->item('email_subject', 'smartc_auth_config') . ' - ' . $this->lang->line('email_activation_subject'));
                $this->email->message($email_message);

                if ($this->email->send() === TRUE)
                {
                    $this->set_message('account_email_activation_success');
                    return $id;
                }
                else
                {
                    $this->set_message('account_create_success');
                    $this->set_message('account_activation_email_failed');
                    return FALSE;
                }
            }

            $this->set_message('account_create_success');
            return $id;
        }
        else
        {
            $this->set_message('account_create_failed');
            return FALSE;
        }
    }

    /**
     * Login
     * 
     * @param string    $identity   User Identity
     * @param string    $password   User Password
     * @param bool      $rememberme Remember me ?
     * @return bool
     */

    public function login($identity, $password, $rememberme = FALSE)
    {
        $user_data = $this->select('user_id,username,user_password,user_email,user_status,'.$this->identity_column)
                          ->user($identity)
                          ->row();

        if (empty($user_data))
        {
            $this->set_message('account_error_unregistered');
            return FALSE;
        }

        $max_login = $this->config->item('max_login_attempts', 'smartc_auth_config');

        if ($this->get_attempts_num($user_data->user_id) >= $max_login)
        {
            $this->set_message('account_error_lockout');
            return FALSE;
        }

        if (intval($user_data->user_status) === 1)
        {
            $hashed_password = base64_encode(hash('sha256', $password, TRUE));

            if (password_verify($hashed_password, $user_data->user_password) === TRUE)
            {
                $token_identifier = bin2hex($this->security->get_random_bytes(16)); // Stored in cookie
                $token = bin2hex($this->security->get_random_bytes(128)); // Stored in cookie and session
                $token_hash = hash('sha256', $token); // Stored in database

                $browser = $this->agent->browser() . ':' . $this->agent->version();
                $platform = $this->agent->platform();
                $expire = $this->cookies['expiration'];
                $ip_address = $this->input->ip_address();

                if ($rememberme === TRUE)
                {
                    $expire = $this->cookies['remember_expiration'];

                    if($this->cookies['remember_expiration'] === 0)
                    {
                        // Set expire to 1 Day
                        $expire = DAY_IN_SECONDS;
                    }
                }

                $login_data = array(
                    'user_id' => $user_data->user_id,
                    'identifier' => $token_identifier,
                    'token' => $token_hash,
                    'user_agent' => $browser,
                    'platform' => $platform,
                    'ip_address' => $ip_address,
                    'time' => $time,
                    'expiration_time' => time() + $expire,
                    'status' => 1
                );

                // Other sessions will be logged out
                //$this->smartc_auth_model->deactivate_user_logins($user_data->user_id);

                // Insert login data to database
                $login_id = $this->set($login_data)->add($this->tables['logins']);

                // We need to reset login attempts first
                $this->clear_login_attempts($user_data->user_id);

                $this->set_login_cookie($identity, $token_identifier, $token, $expire);

                $user_data->token = $token;
                $this->set_session($user_data);

                $this->set_message('account_login_success');
                return TRUE;
            }
            else
            {
                $this->increase_login_attempts($user_data->user_id);
                $this->smartc_auth->set_message('account_error_wrong_password');
                return FALSE;
            }
        }
        elseif (intval($user_data->user_status) === 0)
        {
            $this->set_message('account_error_unactivated');
            return FALSE;
        }
        else
        {
            $this->set_message('account_error_banned');
            return FALSE;
        }
    }

    /**
     * Regenerate token cookie and save it to database
     * 
     * @param string $identifier
     * @return bool
     */

    public function regenerate_token($identifier)
    {
        $login_data = $this->login_data($identifier)->row();

        $user_data = $this->select('user_id,username,user_email,'.$this->identity_column)
                          ->user_id($login_data->user_id)
                          ->row();

        if ( ! empty($user_data) && ! empty($login_data))
        {
            $time = time();

            //Deactivate expired token
            if ($time > $login_data->expiration_time)
            {
                $this->deactivate_login($identifier);
                return FALSE;
            }

            // Regenerate token
            $token = bin2hex($this->security->get_random_bytes(128));
            $token_hash = hash('sha256', $token);

            // Regenerate session id
            $this->session->sess_regenerate(TRUE);

            // We store the hashed token on database and plain token in cookie and session
            $data = array(
                'token' => $token_hash,
                'time' => $time,
            );

            $update = $this->set($data)
                           ->where(['login_id' => $login_data->login_id, 'user_id' => $user_data->user_id])
                           ->update($this->tables['logins']);

            if($update === FALSE)
            {
                return FALSE;
            }

            $expire = $login_data->expiration_time - $time;

            $this->update_last_login($identifier);
            $this->set_login_cookie($user_data->{$this->identity_column}, $token_identifier, $token, $expire);

            $user_data->token = $token;
            $this->set_session($user_data);

            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Set user status flag
     *
     * @param   int     $identity   User Identity
     * @param   int     $status     Status code
     * @return  bool
     */

    public function set_user_status($identity, $status)
    {
        $update = $this->where([$this->identity_column => $identity])
                       ->set(['user_status' => $status])
                       ->update($this->tables['users']);
        return $update;
    }

    /**
     * Groups
     *
     * @return  static
     */

    public function groups()
    {
        return $this->get($this->tables['groups']);
    }


    /**
     * Get user groups
     *
     * @param int $user_id User ID
     * @return mixed
     */

    public function user_groups($user_id)
    {
        $query = $this->db->select("{$this->tables['groups']}.group_id, {$this->tables['groups']}.group_name")
                          ->where("{$this->tables['user_groups']}.user_id", $user_id)
                          ->join($this->tables['groups'],
                            "{$this->tables['groups']}.group_id = {$this->tables['user_groups']}.{$this->join['groups']}", 'inner')
                          ->get($this->tables['user_groups']);
        $this->response = $query;
        return $this;
    }

    /**
     * Add group
     *
     * @param string $name Group name
     * @param string $desc Group description
     * @return mixed
     */
    public function add_group($name, $desc = '')
    {
        $data = array(
            'group_name' => $name,
            'group_description' => $desc
        );

        return $this->add($this->tables['groups'], $data);
    }

    /**
     * Remove group(s)
     *
     * @param   array|int   $group_id   Group IDs
     * @return  bool
     */

    public function remove_group($group_ids)
    {
        if ( ! is_array($group_ids))
        {
            $group_ids = array($group_ids);
        }
        
        return $this->where_in('group_id', $group_ids)
                    ->delete($this->tables['groups']);
    }

    /**
     * Add user to group
     *
     * @param   int $user_id    User ID
     * @param   int $group_id   Group ID
     * @return  bool
     */

    public function add_to_group($user_id, $group_id)
    {
        $this->set([
                'user_id' => $user_id,
                'group_id' => $group_id
            ]
        );
        return (bool)$this->add($this->tables['user_groups']);
    }

    /**
     * Move user to specific groups
     *
     * @param   int         $user_id    User ID
     * @param   array|int   $group_ids  Group IDs. 
     * @return  bool
     */

    public function move_to_group($user_id, $group_ids)
    {
        if ( ! is_array($group_ids))
        {
            $group_ids = array($group_ids);
        }
        $this->remove_user_groups($user_id);

        $batch = array();
        foreach ($group_ids as $group_id) 
        {
            $data['user_id'] = $user_id;
            $data['group_id'] = $group_id;
            array_push($batch, $data);
        }
        return (bool)$this->add_bulk($this->tables['user_groups'], $batch);
    }

    /**
     * Remove user from specific group
     *
     * @param   int     $user_id    User ID
     * @param   int     $group_id   Group ID. 
     * @return  bool
     */

    public function remove_from_group($user_id, $group_id)
    {
        $this->where(array('user_id' => $user_id, 'group_id' => $group_id));
        return $this->delete($this->tables['user_groups']);
    }

    /**
     * Remove user from all groups
     *
     * @param   int     $user_id    User ID 
     * @return  bool
     */

    public function remove_user_groups($user_id)
    {
        return $this->where('user_id', $user_id)->delete($this->tables['user_groups']);
    }

    /**
     * Get permissions list
     *
     * @return  static
     */
    public function permissions()
    {
        return $this->get($this->tables['permissions']);
    }

    /**
     * Add permission(s)
     *
     * @param   array|string    $name   Permission name
     * @return  mixed           Insert id or affected rows. But FALSE on failure.
     */
    public function add_permission($names)
    {
        if (is_array($names))
        {
            $insert = array();
            foreach($names as $name)
            {
                $insert[]['permission_name'] = strtolower($name);
            }
            return $this->add_bulk($this->tables['permissions'], $insert);
        }
        else
        {
            $this->set('permission_name', strtolower($names));
            return $this->add($this->tables['permissions']);
        }
    }

    /**
     * Remove permission(s)
     *
     * @param   array|int   $permission_ids Permission IDs
     * @return  bool
     */

    public function remove_permission($permission_ids)
    {
        if ( ! is_array($permission_ids))
        {
            $permission_ids = array($permission_ids);
        }
        
        return $this->where_in('permission_id', $permission_ids)
                    ->delete($this->tables['permissions']);
    }

    /**
     * Change group permissions
     *
     * @param   int         $group_id       Group ID
     * @param   array|int   $permission_ids Permission IDs. 
     * @return  bool
     */

    public function allow_group($group_id, $permission_ids)
    {
        if ( ! is_array($permission_ids))
        {
            $permission_ids = array($permission_ids);
        }
        $this->remove_group_permissions($group_id);

        $batch = array();
        foreach ($permission_ids as $permission_id) 
        {
            $data['group_id'] = $group_id;
            $data['permission_id'] = $permission_id;
            array_push($batch, $data);
        }
        return (bool)$this->add_bulk($this->tables['group_permissions'], $batch);
    }

    /**
     * Remove permission from group
     *
     * @param int $user_id  Group ID
     * @param int $group_id Permission IDs. 
     * @return  bool
     */

    public function deny_group($group_id, $permission_id)
    {
        $this->where(array('group_id' => $group_id, 'permission_id' => $group_id));
        return $this->delete($this->tables['group_permissions']);
    }

    /**
     * Remove all permissions from group
     *
     * @param   int     $user_id    User ID 
     * @return  bool
     */

    public function remove_group_permissions($group_id)
    {
        return $this->where('group_id', $group_id)->delete($this->tables['group_permissions']);
    }

    /**
     * Get group(s) permissions
     *
     * @param   array|int   $group_id   Group ID
     * @return  array Array of permissions or empty
     */

    public function groups_permissions($group_ids = array())
    {
        $return = array();
        $query = $this->db->select("{$this->tables['permissions']}.permission_name")
                          ->where_in("{$this->tables['group_permissions']}.group_id", $group_ids)
                          ->join($this->tables['permissions'], 
                            "{$this->tables['group_permissions']}.permission_id = {$this->tables['permissions']}.permission_id")
                          ->join($this->tables['groups'], 
                            "{$this->tables['group_permissions']}.group_id = {$this->tables['groups']}.group_id")
                          ->get($this->tables['group_permissions']);
        $result = $query->result();

        foreach ($result as $value) 
        {
            $return[] = $value->permission_name;
        }

        $query->free_result();

        return $return;
    }

    /**
     * Get all logins data
     * 
     * @return static
     */

    public function logins()
    {
        return $this->get($this->tables['logins']);
    }

    /**
     * Get user logins data
     * 
     * @param int       $user_id        User ID
     * @param bool      $active_only    Only get active login
     * @return static
     */

    public function user_logins($user_id, $active_only = FALSE)
    {
        $this->where('user_id', $user_id);
        if($active_only === TRUE)
        {
            $this->where(array('status' => 1, 'expiration_time >' => time()));
        }
        $this->order_by("{$this->tables['logins']}.login_id", 'desc');
        $this->logins();

        return $this;
    }

    /**
     * Get login data from login id
     * 
     * @param int   $login_id 
     * @return static
     */

    public function login_data($identifier)
    {
        $this->where('identifier', $identifier);
        $this->limit(1);
        $this->logins();

        return $this;
    }

    /**
     * Deactivate all user logins session
     * 
     * @param int   $user_id    User ID
     * @return bool
     */

    public function deactivate_user_logins($user_id)
    {
        return $this->where(
                        array(
                            'user_id' => $user_id,
                            'status' => 1
                        )
                    )
                    ->set('status', 0)
                    ->update($this->tables['logins']);
    }

    /**
     * Deactivate specific login id
     * 
     * @param int $login_id 
     * @return bool
     */

    public function deactivate_login($identifier)
    {
        return $this->where('identifier', $identifier)
                    ->set('status', 0)
                    ->update($this->tables['logins']);
    }

    /**
     * Update last login timestamp
     * 
     * @param int $login_id 
     * @return bool
     */
    public function update_last_login($identifier)
    {
        $this->where('identifier', $identifier)
             ->set('time', time());
        return $this->update($this->tables['logins']);
    }

    /**
     * Get all login attempts or only specific user
     * 
     * @param array $users  Arry of user identity
     * @return static
     */

    public function login_attempts($user_ids = array())
    {
        if ( ! empty($user_ids))
        {
            if ( ! is_array($user_ids))
            {
                $user_ids = array($user_ids);
            }
            $this->where_in('user_id', $user_ids);
        }
        
        return $this->get($this->tables['login_attempts']);
    }

    /**
     * Get total login attempts
     * 
     * @param int $user_id 
     * @return int
     */

    public function get_attempts_num($user_id)
    {
        if ($this->config->item('track_login_attempts', 'smartc_auth_config'))
        {
            $this->db->where('user_id', $user_id);
            $this->db->where('time >', time() - $this->config->item('lockout_time', 'smartc_auth_config'), FALSE);
            $query = $this->db->get($this->tables['login_attempts']);
            return $query->num_rows();
        }
        return 0;
    }

    /**
     * Increase user login attempts
     * 
     * @param int $user)id
     * @return bool
     */

    public function increase_login_attempts($user_id)
    {
        if ($this->config->item('track_login_attempts', 'smartc_auth_config'))
        {
            $this->db->set(
                array(
                    'user_id' => $user_id,
                    'ip_address' => $this->input->ip_address(),
                    'time' => time()
                )
            );
            return $this->db->insert($this->tables['login_attempts']);
        }
        return FALSE;
    }

    /**
     * Clear login attempts
     * 
     * @param int $user_id
     * @return bool
     */

    public function clear_login_attempts($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('time >', time() - $this->config->item('lockout_time', 'smartc_auth_config'), FALSE);
        return $this->db->delete($this->tables['login_attempts']);
    }

        /**
     * Set message delimeter
     *
     * Set message delimeters for output
     *
     * @param   string  $start  Start delimeter
     * @param   string  $end    End delimeter
     * @return  void
     */
    public function set_message_delimeter($start, $end)
    {
        $this->message_start_delimeter = $start;
        $this->message_end_delimeter = $end;
    }

    /**
     * Set message from smartc_auth_lang array or set your own message for output
     *
     * @param array|string $message Message line
     * @return  void
     */

    public function set_message($message)
    {
        if(is_array($message))
        {
            foreach($message as $value)
            {
                $this->messages[] = $value;
            }
        }
        else
        {
            $this->messages[] = $message;
        }
    }

    /**
     * Get messages
     *
     * @return string
     */

    public function message()
    {
        $messages = '';

        foreach ($this->messages as $m)
        {
            $m_lang = $this->lang->line($m) ? $this->lang->line($m) : $m;
            $messages .= $this->message_start_delimeter . $m_lang . $this->message_end_delimeter . $this->message_new_line;
        }
        return $messages;
    }

    public function message_array()
    {
        $messages = array();

        foreach ($this->messages as $m)
        {
            $m_lang = $this->lang->line($m) ? $this->lang->line($m) : $m;
            $messages[] = $this->message_start_delimeter . $m_lang . $this->message_end_delimeter . $this->message_new_line;
        }
        return $messages;
    }

    /**
     * Clear messages
     * 
     * @return void
     */
    public function clear_messages()
    {
        $this->messages = array();
    }

    /**
     * Set login cookie
     *
     * Set cookie login for remember user login
     *
     * @param   string  $identity           Cookie Identity
     * @param   string  $token              Cookie Token
     * @param   string  $token_identifier   Cookie token identifier
     * @param   int     $expire             Cookie expiration time
     * @return  void
     */

    public function set_login_cookie($identity, $token_identifier, $token, $expire)
    {
        set_cookie(array(
            'name' => $this->cookies['token_identifier'],
            'value' => $token_identifier,
            'expire' => $expire
        ));

        set_cookie(array(
            'name' => $this->cookies['token'],
            'value' => $token,
            'expire' => $expire
        ));

        set_cookie(array(
            'name' => $this->cookies['identity'],
            'value' => $identity,
            'expire' => $expire
        ));
    }

    /**
     * Set session
     *
     * Set user session with result from users table
     *
     * @param   object  $user   User data result object
     * @return  void
     */

    public function set_session($user)
    {
        $session_data = array(
            'identity' => $user->{$this->identity_column},
            $this->identity_column => $user->{$this->identity_column},
            'token' => $user->token,
            'user_id' => $user->user_id,
            'last_login' => time()
        );

        $this->session->set_userdata($session_data);
    }

	/**
	 * Activate user and set message whether it is success or failed
	 *
	 * @param	string	$identity	User identity based on config 'identity'
	 * @param	string	$code		User activation code. (Optional)
	 * @return	bool
	 */

	public function activate($identity, $code = '')
	{
		$user = $this->select('user_activation_code')->user($identity)->row();

		if ( ! empty($code))
		{
			if ( ! hash_equals($code, $user->user_activation_code))
			{
				$this->set_message('account_activation_failed');
				return FALSE;
			}
		}

		unset($user);

		if ($this->set_user_status($identity, 1))
		{
			$this->set_message('account_activation_success');
			return TRUE;
		}
		else
		{
			$this->set_message('account_activation_failed');
			return FALSE;
		}
	}

	/**
	 * Deactivate
	 *
	 * Deactivate user and set message whether it is success or failed
	 *
	 * @param	string	$identity	User identity based on config 'identity' array
	 * @return	bool
	 */

	public function deactivate($identity)
	{
		if ($this->set_user_status($identity, 0))
		{
			$this->set_message('account_deactivation_success');
			return TRUE;
		}
		else
		{
			$this->set_message('account_deactivation_failed');
			return FALSE;
		}
	}

	/**
	 * Ban
	 *
	 * Ban user and set message whether it is success or failed
	 *
	 * @param	string	$identity	User identity based on config 'identity' array
	 * @return	bool
	 */

	public function ban($identity)
	{
		if ($this->set_user_status($identity, -1))
		{
			$this->set_message('account_ban_success');
			return TRUE;
		}
		else
		{
			$this->set_message('account_ban_failed');
			return FALSE;
		}
	}

	/**
	 * Is Active
	 *
	 * Check user status whether is active or not
	 *
	 * @param	string	$identity	User identity based on config 'identity' array
	 * @return	bool
	 */

	public function is_active($identity)
	{
		$user = $this->select('user_status')->user($identity)->row();

		if( ! empty($user) && intval($user->user_status) === 1)
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Is logged in
	 *
	 * Check user is logged in or not
	 *
	 * @return	bool
	 */

	public function is_logged_in()
	{
		$identity = get_cookie($this->cookies['identity']);
		$token = get_cookie($this->cookies['token']);
		$token_identifier = get_cookie($this->cookies['token_identifier']);

		if ( ! empty($identity) && ! empty($token_identifier))
		{
			return $this->login_verify($token_identifier, $token);
		}
		else
		{
			return $this->login_remembered_user($token_identifier, $token);
		}
	}

	/**
	 * Verify login session
	 * 
	 * @param string $token_identifier
	 * @param string $token
	 * @return bool
	 */
	public function login_verify($identifier, $token)
	{
		$recheck = $this->config->item('login_recheck', 'smartc_auth_config');
		$token_hash = hash('sha256', $token);

		if ($this->session->has_userdata('token'))
		{
			$token_session_hash = hash('sha256', $this->session->userdata('token'));

			if ( ! hash_equals($token_hash, $token_session_hash))
			{
				return FALSE;
			}

			unset($token_session_hash);

			if ($recheck + $this->session->userdata('last_login') < time())
			{
				$this->session->set_userdata('last_login', time());
				$this->update_last_login($token_identifier);
			}

			return (bool)$this->session->userdata('token');
		}

		// If we can't found the session we look on the database
		$login_data = $this->select('identifier,token,expiration_time')->login_data($identifier)->row();
		
		if (empty($login_data) && (time() > $login_data->expiration_time))
		{
			return FALSE;
		}

		return hash_equals($login_data->token, $token_hash);
	}

	/**
	 * Login remembered user
	 * 
	 * @return bool
	 */

	public function login_remembered_user($token_identifier, $token)
	{
		$identity = get_cookie($this->cookies['identity']);
		//$token = get_cookie($this->cookies['token']);
		//$token_identifier = get_cookie($this->cookies['token_identifier']);

		if ($this->login_verify($token_identifier, $token) === TRUE)
		{
			return $this->regenerate_token($token_identifier);
		}
		
		return FALSE;
	}

	/**
	 * Logout
	 * 
	 * @return void
	 */

	public function logout()
	{
		$identity = $this->config->item('identity', 'smartc_auth_config');

		$this->session->unset_userdata(array($identity, 'user_id', 'identity', 'token'));

		if (get_cookie($this->cookies['identity']))
		{
			delete_cookie($this->cookies['identity']);
		}

		if (get_cookie($this->cookies['token_identifier']))
		{
			$this->deactivate_login(get_cookie($this->cookies['token_identifier']));
			delete_cookie($this->cookies['token']);
			delete_cookie($this->cookies['token_identifier']);
		}

		// Destroy the session
		$this->session->sess_destroy();
		$this->session->sess_regenerate(TRUE);

		$this->set_message('account_logout_success');
	}

	/**
	 * Change password
	 * 
	 * @param string $identity 
	 * @param string $new_password 
	 * @param string $old_password
	 * @return bool
	 */
	public function change_password($identity, $new_password, $old_password = NULL)
	{
		$new_hashed_password = password_hash(
			base64_encode(
				hash('sha256', $new_password, TRUE)
			), 
			$this->salt_type
		);

		if (isset($old_password))
		{
			$user_data = $this->select('user_password')->user($identity)->row();
			$old_password = base64_encode(hash('sha256', $old_password, TRUE));

			if (password_verify($old_password, $user_data->user_password) === FALSE)
			{
				$this->set_message('account_password_change_failed');
				return FALSE;
			}

			unset($user_data);
		}

		$edit = $this->user_update($identity, array('user_password' => $new_hashed_password));

		if($edit === TRUE)
		{
			$this->set_message('account_password_change_success');
			return TRUE;
		}
		else
		{
			$this->set_message('account_password_change_failed');
			return FALSE;
		}
	}

	/**
	 * Check user if in group(s)
	 * 
	 * @param string|array 	$groups 	Array of Groups id or Groups name.
	 * @param null|int 		$user_id	User ID
	 * @param bool 			$check_all	Whether to checking all groups or not.
	 * 
	 * @return bool
	 */

	public function in_group($check_groups, $user_id = NULL, $check_all = FALSE)
	{
		$user_id = (is_null($user_id)) ? $this->session->userdata('user_id') : $user_id;
		$user_groups = $this->user_groups($user_id)->result();
		$groups_array = array();

		if ( ! is_array($check_groups))
		{
			$check_groups = array($check_groups);
		}

		foreach ($user_groups as $group)
		{
			$groups_array[$group->group_id] = $group->group_name;
		}

		foreach ($check_groups as $value) 
		{
			$groups = (is_string($value)) ? $groups_array : array_keys($groups_array);

			if (in_array($value, $groups) xor $check_all)
			{
				return !$check_all;
			}
		}
		return $check_all;
	}

	/**
	 * Check user permission(s)
	 * 
	 * @param string|array 	$permissions 	Array of permissions to check
	 * @param int 			$user_id 		
	 * @param bool 			$check_all 
	 * @return bool
	 */

	public function user_has_permission($permissions, $user_id = NULL, $check_all = FALSE)
	{
		$user_id = (is_null($user_id)) ? $this->session->userdata('user_id') : $user_id;

		if ( ! is_array($permissions))
		{
			$permissions = array($permissions);
		}

		$user_groups = $this->user_groups($user_id)->result();
		$groups_permissions = array();

		foreach ($user_groups as $group)
		{
			$groups_permissions[$group->group_name] = $this->groups_permissions($group->group_id);
		}

		foreach ($permissions as $value) 
		{
			foreach ($groups_permissions as $permission) 
			{
				if (in_array($value, $permission) XOR $check_all)
				{
					return !$check_all;
				}
			}
		}
		return $check_all;
	}
}
