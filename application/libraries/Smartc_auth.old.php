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

class Smartc_auth
{
	/**
	 * Cookies name
	 *
	 * @var	array
	 */
	public $cookies = array();

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

	public function __construct($config = 'smartc_auth_config')
	{
		$this->config->load($config, TRUE);
		
		$this->allowed_chars = $this->config->item('username_allowed_chars', 'smartc_auth_config');
		$this->username_max_length = $this->config->item('username_max_length', 'smartc_auth_config');
		$this->username_min_length = $this->config->item('username_min_length', 'smartc_auth_config');

		$this->cookies = $this->config->item('cookies', 'smartc_auth_config');
		$this->salt_type = $this->config->item('salt_type', 'smartc_auth_config');

		$this->load->library(array('form_validation', 'email', 'session', 'user_agent'));
		$this->load->model('smartc_auth_model');

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
