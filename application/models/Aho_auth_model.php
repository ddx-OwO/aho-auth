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

require 'Aho_model.php';

use Firebase\JWT\JWT;

class Aho_auth_model extends Aho_Model {

    private $jwt;

    public function __construct() 
    {
        parent::__construct();

        $this->load->library(array('form_validation', 'email', 'session', 'user_agent'));

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
        $user_data = $this->db
                          ->select('user_id,username,password,email,status,'.$this->identity_column)
                          ->get($this->tables['users'])
                          ->row();

        if (empty($user_data))
        {
            $this->set_message('account_error_unregistered');
            return FALSE;
        }

        $max_login = $this->config->item('max_login_attempts', 'aho_config');

        if ($this->get_attempts_num($user_data->user_id) >= $max_login)
        {
            $this->set_message('account_error_lockout');
            return FALSE;
        }

        if (intval($user_data->status) === 1)
        {
            $hashed_password = base64_encode(hash('sha256', $password, TRUE));

            if (password_verify($hashed_password, $user_data->password) === TRUE)
            {
                $ua = $this->agent->agent_string();
                $platform = $this->agent->platform();
                $ip_address = $this->input->ip_address();
                $exp = $rememberme ? $this->jwt['remember_expiration'] : $this->jwt['expiration'];
                $time = time();
                $r_token = $this->token_generate();
                $payload = array(
                    'user_id' => $user_data->user_id,
                    'identity' => $user_data->{$this->identity_column},
                    'refresh_token' => $r_token
                );
                $jwt = $this->jwt_generate($payload, $exp, $time);
                $login_data = array(
                    'user_id' => $user_data->user_id,
                    'refresh_token' => $r_token,
                    'user_agent' => $ua,
                    'platform' => $platform,
                    'ip_address' => $ip_address,
                    'expires_in' => date('Y-m-d H:i:s', $this->jwt['refresh_token_expiration']+$time)
                );

                // Insert login data to database
                $login_id = $this->set($login_data)->add($this->tables['logins']);

                $this->clear_login_attempts($user_data->user_id);
                // $this->set_login_cookie($user_data->user_id, $jwt, $exp);
                // $this->set_session($user_data);

                $this->set_message('account_login_success');
                return array(
                    'access_token' => $jwt, 
                    'expires_in' => $exp,
                    'refresh_token' => $r_token
                );
            }
            else
            {
                $this->increase_login_attempts($user_data->user_id);
                $this->set_message('account_error_wrong_password');
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

    public function jwt_generate($data, $expire = 7200, $now = time())
    {
        $iss = config_item('base_url');
        $aud = $this->input->server('HTTP_REMOTE_ADDR');
        $iat = $now;
        $nbf = $now + 5;
        $exp = $expire;
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

    public function token_verify($token, $user_id)
    {
        $data = $this->login_data($token, $user_id)->row();

        $this->set_message('account_error_token_expired');
    }

    /**
     * Verify login session
     * 
     * @param string $jwt
     * @return string|bool
     */
    /*public function login_verify($jwt)
    {
        try {
            $decoded = JWT::decode($jwt, $this->jwt['key'], array($this->jwt['algo']));
            $data = $decoded->data;
            $login_data = $this->login_data($data->user_id, $data->token)->row();

            if ( ! empty($login_data))
            {
                $match = hash_equals($login_data->token, $data->token);
                return $match ? $data : FALSE;
            }
            unset($login_data);
            $this->set_message('account_error_token_expired');
            return FALSE;
        } catch (Exception $e) {
            $this->set_message($e->getMessage());
            return FALSE;
        }
    }*/

    /*public function is_logged_in()
    {
        $token = get_cookie($this->cookies['token']);
        return
    }*/

    /*public function login_verify($token)
    {
        $user_id = $this->session->userdata('user_id') ? $user_id : get_cookie($this->cookies['token']);
        $login_data = $this->where('user_id', $user_id)
                           ->login_data($token)
                           ->row();

        if ( ! empty($login_data))
        {
            $match = hash_equals($login_data->token, $token);

            if ($match && ($login_data->expires_in < time()))
            {
                $this->set_message('account_error_token_expired');
                return FALSE;
            }
            return $match;
        }
        $this->set_message('account_error_token_expired');
        return FALSE;
    }*/

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
     * Get all login attempts or only specific user
     * 
     * @param array $users  Arry of user identity
     * @return static
     */

    public function login_attempts($user_id = array())
    {
        if ( ! empty($user_id))
        {
            if ( ! is_array($user_id))
            {
                $this->where_in('user_id', $user_id);
            }
            else
            {
                $this->where('user_id', $user_ids);
            }
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
     * @return static
     */

    public function logins()
    {
        return $this->get($this->tables['logins']);
    }

    /**
     * Get login data from login id
     * 
     * @param int   $login_id 
     * @return static
     */

    public function login_data($token, $user_id = NULL)
    {
        if (isset($user_id))
        {
            $this->where('user_id', $user_id);
        }

        $this->where('token', $token);
        $this->limit(1);
        $this->logins();

        return $this;
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
