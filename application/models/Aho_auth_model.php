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

use Firebase\JWT\JWT;

class Aho_auth_model extends CI_Model {

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
     * @var array
     */
    private $jwt = array();

    public function __construct() 
    {
        parent::__construct();

        $this->load->library(array('form_validation', 'email', 'user_agent', 'message'));
        $this->load->model('aho_model');
        $this->load->model('aho_user_model', 'aho_user');

        $this->tables = $this->config->item('tables', 'aho_config');
        $this->identity_column = $this->config->item('identity_column', 'aho_config');
        $this->jwt = $this->config->item('jwt', 'aho_config');
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
     * Login
     * 
     * @param string    $identity   User Identity
     * @param string    $password   User Password
     * @param bool      $rememberme Remember me ?
     * @return bool
     */

    public function login($identity, $password, $rememberme = FALSE)
    {
        $this->db->select('user_id,username,password,email,status,'.$this->identity_column);
        $user_data = $this->aho_user
                          ->user($identity)
                          ->row();


        if (empty($user_data))
        {
            $this->message->set_message('account_error_unregistered');
            return FALSE;
        }

        $max_login = $this->config->item('max_login_attempts', 'aho_config');

        if ($this->get_attempts_num($user_data->user_id) >= $max_login)
        {
            $this->message->set_message('account_error_lockout');
            return FALSE;
        }

        if (intval($user_data->status) === 1)
        {
            $hashed_password = base64_encode(
                hash('sha256', $password, TRUE)
            );

            if (password_verify($hashed_password, $user_data->password) === TRUE)
            {
                $ua = $this->agent->agent_string();
                $platform = $this->agent->platform();
                $ip_address = $this->input->ip_address();
                $r_token = $this->token_generate();
                $r_token_exp = $rememberme ? $this->jwt['remember_expiration'] : $this->jwt['refresh_token_expiration'];
                $now = time();

                $payload = array(
                    'user_id' => $user_data->user_id,
                    $this->identity_column => $user_data->{$this->identity_column},
                    'email' => $user_data->email,
                    'refresh_token' => $r_token
                );

                $jwt = $this->jwt_generate($payload, $now, $this->jwt['expiration']);

                $login_data = array(
                    'user_id' => $user_data->user_id,
                    'refresh_token' => $r_token,
                    'user_agent' => $ua,
                    'platform' => $platform,
                    'ip_address' => $ip_address,
                    'expires_in' => date('Y-m-d H:i:s', $now+$r_token_exp)
                );

                // Insert login data to database
                $login_id = $this->aho_model
                                 ->set($login_data)
                                 ->safe_insert($this->tables['logins']);

                $this->clear_login_attempts($user_data->user_id);
                // $this->set_login_cookie($user_data->user_id, $jwt, $exp);
                // $this->set_session($user_data);

                $this->message->set_message('account_login_success');
                return array(
                    'access_token' => $jwt, 
                    'expires_in' => $this->jwt['expiration'],
                    'refresh_token' => $r_token
                );
            }
            else
            {
                $this->increase_login_attempts($user_data->user_id);
                $this->message->set_message('account_error_wrong_password');
                return FALSE;
            }
        }
        elseif (intval($user_data->user_status) === 0)
        {
            $this->message->set_message('account_error_unactivated');
            return FALSE;
        }
        else
        {
            $this->message->set_message('account_error_banned');
            return FALSE;
        }
    }

    public function jwt_generate($data, $iat, $expire = 7200)
    {
        $iss = config_item('base_url');
        $aud = $this->input->server('HTTP_REMOTE_ADDR');
        $iat = $iat;
        $nbf = $iat + 5;
        $exp = $expire + $iat;
        $claims = array(
            "iss" => $iss,
            "aud" => $aud,
            "iat" => $iat,
            "nbf" => $nbf,
            "exp" => $exp,
            "data" => $data
        );

        return JWT::encode($claims, $this->jwt['key'], $this->jwt['algo']);
    }

    /**
     * Generate random string for token
     * @return string
     */
    public function token_generate()
    {
        return bin2hex(random_bytes(32));
    }

    public function login_refresh($refresh_token, $user_id)
    {
        $data = $this->login_data($refresh_token, $user_id)->row();
        $now = time();
        if (empty($data))
        {
            $this->message->set_message('account_error_token_invalid');
            return FALSE;
        }

        $match = hash_equals($data->refresh_token, $refresh_token);
        if ($match)
        {
            // Prevent generating new token if the old token is not expired yet
            if ($this->jwt['expiration'] > ($now - strtotime($data->login_at)))
            {
                $this->message->set_message('You just refreshed your token');
                return FALSE;
            }

            $exp = strtotime($data->expires_in);
            if ($exp < $now || intval($data->revoked) === 1)
            {
                $this->login_revoke($token, $user_id);
                $this->message->set_message('account_error_token_expired');
                return FALSE;
            }

            $new_token = $this->token_generate();

            $this->db->select('user_id,username,email,'.$this->identity_column);
            $user_data = $this->aho_user
                              ->user_id($user_id)
                              ->result();
            $payload = array('refresh_token' => $new_token);
            $payload = array_merge($payload, $user_data[0]);
            $jwt = $this->jwt_generate($payload, $now, $this->jwt['expiration']);

            // Update refresh token on database
            $update = $this->db
                           ->set('refresh_token', $new_token)
                           ->where([
                                'user_id' => $user_id,
                                'refresh_token' => $refresh_token
                            ])
                           ->update($this->tables['logins']);
            return array(
                'access_token' => $jwt, 
                'expires_in' => $this->jwt['expiration'],
                'refresh_token' => $new_token
            );
        }
        
        $this->message->set_message('account_error_token_invalid');
        return FALSE;
    }

    /**
     * Set token revoked flag
     * @param string $token
     * @return string
     */
    public function login_revoke($token = NULL, $user_id = NULL)
    {
        $this->db->set('revoked', 1);
        if (isset($token))
        {
            $this->db->where('refresh_token', $token);
        }

        if (isset($user_id))
        {
            $this->db->where('user_id', $user_id);
        }
        return $this->db->update($this->tables['logins']);
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
     * Get all login attempts or only specific user
     * 
     * @param int|array $user_id
     * @return CI_DB_Result
     */

    public function login_attempts($user_id = array())
    {
        if ( ! empty($user_id))
        {
            $where = is_array($user_id) ? 'where_in' : 'where';
            return $this->db->{$where}->get($this->tables['login_attempts']);
        }
        
        return $this->db->get($this->tables['login_attempts']);
    }

    /**
     * Get total login attempts
     * 
     * @param int $user_id 
     * @return int
     */

    public function get_attempts_num($user_id)
    {
        if ($this->config->item('track_login_attempts', 'aho_config'))
        {
            $diff = time() - $this->config->item('lockout_time', 'aho_config');
            $this->db->where('user_id', $user_id);
            $this->db->where('time >', $diff, FALSE);

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
        if ($this->config->item('track_login_attempts', 'aho_config'))
        {
            $ua = $this->agent->agent_string();
            $platform = $this->agent->platform();
            $ip_address = $this->input->ip_address();

            $this->db->set(
                array(
                    'user_id' => $user_id,
                    'ip_address' => $ip_address,
                    'user_agent' => $ua,
                    'platform' => $platform,
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
        if ($this->config->item('track_login_attempts', 'aho_config'))
        {
            $diff = time() - $this->config->item('lockout_time', 'aho_config');
            $this->db->where('user_id', $user_id);
            $this->db->where('time >', $diff, FALSE);

            return $this->db->delete($this->tables['login_attempts']);
        }
        return FALSE;
    }

    /**
     * Get all logins data
     * 
     * @return CI_DB_result
     */

    public function logins()
    {
        return $this->db->get($this->tables['logins']);
    }

    /**
     * Get login data from login id
     * 
     * @param int   $login_id 
     * @return CI_DB_result
     */

    public function login_data($token, $user_id = NULL)
    {
        if (isset($user_id))
        {
            $this->db->where('user_id', $user_id);
        }

        $this->db->where('refresh_token', $token)
                 ->limit(1);

        return $this->logins();
    }

    /**
     * Update last login timestamp
     * 
     * @param int $login_id 
     * @return bool
     */
    public function update_last_login($identifier)
    {
        $this->db->where('identifier', $identifier)
                ->set('time', time());
        return $this->update($this->tables['logins']);
    }

    /**
     * Set login cookie
     *
     * Set cookie login for remember user login
     *
     * @param   string  $identity           Cookie Identity
     * @param   string  $token              Cookie Token
     * @param   int     $expire             Cookie expiration time
     * @return  void
     */

    public function set_login_cookie($identity, $token, $expire)
    {
        set_cookie(array(
            'name' => $this->cookies['identity'],
            'value' => $identity,
            'expire' => $expire
        ));

        set_cookie(array(
            'name' => $this->cookies['token'],
            'value' => $token,
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
            'user_id' => $user->user_id,
            'last_login' => time()
        );

        $this->session->set_userdata($session_data);
    }
}
