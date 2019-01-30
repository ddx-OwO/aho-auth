<?php
/**
 * Name:    Aho Auth
 * Author:  Dewa Andhika Putra
 *          dewaandhika18@gmail.com
 *          @dwzzzl
 *
 * Created:  2019.01.25
 *
 * Requirements: PHP 7.1 or above
 *
 * @package    aho-auth
 * @author     Dewa Andhika Putra
 * @link       http://github.com/dwzzzl/aho-auth
 * @since      Version 0.1.0
 */

defined('BASEPATH') or exit('No direct access script allowed');

// require 'Aho_model.php';

class Aho_user_model extends CI_Model {

    /**
     * Tables
     * 
     * @var array
     */
    public $tables = array();

    /**
     * User identity column
     * 
     * @var string
     */
    public $identity_column;

    /**
     * @var array
     */
    public $join = array();

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
     * Allowed characters for username
     *
     * @var string
     */
    protected $allowed_chars;

    /**
     * Username maximum length
     *
     * @var int
     */
    protected $username_max_length;

    /**
     * Username minimum length
     *
     * @var int
     */
    protected $username_min_length;

    /**
     * Array of protected User IDs
     *
     * @var array
     */
    private $protected_users = array();

    public function __construct()
    {
        parent::__construct();

        $this->load->library('message');
        $this->load->model('aho_model');
        $this->load->model('aho_group_model', 'aho_group');

        $this->tables = $this->config->item('tables', 'aho_config');
        $this->protected_users = $this->config->item('protected_users', 'aho_config');
        $this->allowed_chars = $this->config->item('username_allowed_chars', 'aho_config');
        $this->username_max_length = $this->config->item('username_max_length', 'aho_config');
        $this->username_min_length = $this->config->item('username_min_length', 'aho_config');
        $this->identity_column = $this->config->item('identity_column', 'aho_config');
        $this->join = $this->config->item('join', 'aho_config');
    }

    /**
     * __get
     *
     * Enables the use of CI super-global without having to define an extra variable.
     *
     * @param    string $var
     * @return    mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }

    /**
     * Get users
     * 
     * @param array $groups
     * @return Aho_model static
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
            $this->db->join($this->tables['groups'],
                "{$this->tables['user_groups']}.{$this->join['groups']} = 
                {$this->tables['groups']}.group_id",
                'inner'
            );
            $this->db->where_in("{$this->tables['user_groups']}.{$this->join['groups']}", $groups);
        }

        return $this->db->get($this->tables['users']);
    }

    /**
     * Is user exist
     * 
     * @param string $identity
     * @return bool
     */
    public function is_user_exist($identity)
    {
        $query = $this->db->where($this->identity_column, $identity)
                          ->limit(1)
                          ->get($this->tables['users']);

        return $query->num_rows() > 0;
    }

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
            $this->message->set_message(
                sprintf(
                    lang('account_create_invalid_identity'), 
                    lang('username_label')
                )
            );
            $this->form_validation->set_message(
                'username_check', 
                lang('account_create_invalid_identity')
            );
            return FALSE;
        }
        // Check username minimum length
        else if ($length < $this->username_min_length && $this->username_min_length !== 0)
        {
            $this->message->set_message(
                sprintf(
                    lang('account_create_min_length'), 
                    lang('username_label'), 
                    $this->username_min_length
                )
            );
            $this->form_validation->set_message('username_check', 
                sprintf(
                    lang('account_create_min_length'), 
                    lang('username_label'), 
                    $this->username_min_length
                )
            );
            return FALSE;
        }
        // Check username maximum length
        else if ($length > $this->username_max_length && $this->username_max_length !== 0)
        {
            $this->message->set_message(
                sprintf(
                    lang('account_create_max_length'), 
                    lang('username_label'), 
                    $this->username_max_length
                )
            );
            $this->form_validation->set_message('username_check', 
                sprintf(
                    lang('account_create_max_length'), 
                    lang('username_label'), 
                    $this->username_max_length
                )
            );
            return FALSE;
        }

        // Check existed identity
        if($this->is_user_exist($username) === TRUE)
        {
            $this->message->set_message(
                sprintf(
                    $this->lang->line('account_create_duplicate_identity'), 
                    $this->lang->line('username_label')
                )
            );
            $this->form_validation->set_message('username_check', 
                lang('account_create_duplicate_identity')
            );
            return FALSE;
        }

        $this->message->set_message(
            sprintf(
                lang('account_create_available_identity'), 
                lang('username_label')
            )
        );
        $this->form_validation->set_message('username_check', 
            lang('account_create_available_identity')
        );
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
        return $this->db->where('email', $email)
                        ->limit(1)
                        ->count_all_results($this->tables['users']) > 0;
    }

    /**
     * Get user by identity
     * 
     * @param string $identity 
     * @return CI_DB_Result
     */
    public function user($identity)
    {
        $this->db
             ->where("{$this->tables['users']}.{$this->identity_column}", $identity)
             ->limit(1)
             ->order_by($this->identity_column, 'desc');
        return $this->users();
    }

    /**
     * Get user by id
     * 
     * @param int $id 
     * @return CI_DB_Result
     */
    public function user_id($id)
    {
        $this->db
             ->where("{$this->tables['users']}.user_id", $id)
             ->limit(1);
        return $this->users();
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
        $user_data = $this->db
                          ->select('user_id,username,email,'.$this->identity_column)
                          ->user($identity)
                          ->row();

        if (array_key_exists('username', $update_data))
        {
            if($user_data->username !== strtolower($update_data['username']))
            {
                if($this->username_check(strtolower($update_data['username'])) === TRUE)
                {
                    return FALSE;
                }
            }
        }
        
        if(array_key_exists('email', $update_data))
        {
            if($user_data->user_email !== strtolower($update_data['email']))
            {
                if($this->email_check(strtolower($update_data['email'])))
                {
                    $this->message->set_message(
                        sprintf(
                            $this->lang->line('account_create_duplicate_identity'), 
                            $this->lang->line('email_label')
                        )
                    );
                    return FALSE;
                }
            }
        }

        $update = $this->aho_model
                       ->set($update_data)
                       ->where($this->identity_column, $identity)
                       ->safe_update($this->tables['users']);
        
        if ($update)
        {
            if ( ! empty($groups))
            {
                if( ! is_array($groups))
                {
                    $groups = array($groups);
                }
                // $this->smartc_auth_model->move_to_group($user_data->user_id, $groups);
            }
            $this->message->set_message('account_update_success');
            return TRUE;
        }
        else
        {
            $this->message->set_message('account_update_failed');
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

            $delete = $this->db->where($this->identity_column, $identity)
                           ->delete($this->tables['users']);
            
            if($delete)
            {
                $this->set_message('account_delete_success');
                return TRUE;
            }

            $this->set_message('account_delete_failed');
            return FALSE;
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

        $default_status = ($this->config->item('activation_method', 'aho_config') === FALSE) ? 1 : 0;

        // The password length is truncated if longer than 72 characters
        // See: https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
        $hashed_password = password_hash(
            base64_encode(
                hash('sha256', $password, TRUE)
            ), 
            $this->salt_type
        );

        $data = array(
            $this->identity_column => strtolower($identity),
            'username' => strtolower($identity),
            'password' => $hashed_password,
            'email' => strtolower($email),
            'activation_code' => $this->activation_code,
            'status' => $default_status
        );

        // Merge the additional data
        if (isset($extra) && ! empty($extra))
        {
            $data = array_merge($data, $extra);
        }

        // Get the user id
        $id = $this->aho_model
                   ->set($data)
                   ->safe_insert($this->tables['users']);

        if ($id !== FALSE)
        {
            if (isset($groups) && ! empty($groups))
            {
                foreach ($groups as $group_id) 
                {
                    $this->aho_group->add_to_group($id, $group_id);
                }
            }
            else
            {
                $default_group = $this->config->item('user_default_group', 'aho_config');
                $group = $this->select('group_id')->where('group_name', $default_group)->groups()->row();

                $this->aho_group->add_to_group($id, $group->group_id);

                unset($group, $default_group);
            }

            // Check if activation method is using email and user default status is 0 (Nonactive)
            // Email activation only works when user default status is 0 (Nonactive)
            $activation_method = $this->config->item('activation_method', 'aho_config');
            
            if ($activation_method === 'email')
            {
                $email_message_data = array(
                    'identity' => $identity,
                    'activation_code' => $this->activation_code
                );
                $email_message = $this->load->view($this->config->item('email_templates', 'aho_config').$this->config->item('email_activate', 'aho_config'), $data, TRUE);

                $this->email->clear();

                $this->email->from($this->config->item('admin_email', 'aho_config'), $this->config->item('email_subject', 'aho_config'));
                $this->email->to($email);
                $this->email->subject($this->config->item('email_subject', 'aho_config') . ' - ' . $this->lang->line('email_activation_subject'));
                $this->email->message($email_message);

                if ($this->email->send() === TRUE)
                {
                    $this->message->set_message('account_email_activation_success');
                    return $id;
                }
                else
                {
                    $this->message->set_message('account_create_success');
                    $this->message->set_message('account_activation_email_failed');
                    return FALSE;
                }
            }

            $this->message->set_message('account_create_success');
            return $id;
        }
        else
        {
            $this->message->set_message('account_create_failed');
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
        $update = $this->db
                       ->where($this->identity_column, $identity)
                       ->set('status', $status)
                       ->update($this->tables['users']);
        return $update;
    }
}
