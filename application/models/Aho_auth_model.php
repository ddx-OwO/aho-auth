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

class Aho_auth_model extends Aho_Model {

    public function __construct() 
    {
        parent::__construct();
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


}
