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

defined('BASEPATH') or exit('No direct access allowed');

/*
| -------------------------------------------------------------------------
| Tables
| -------------------------------------------------------------------------
| Database tables name.
*/
$config['tables']['users'] = 'user';
$config['tables']['groups'] = 'group';
$config['tables']['user_groups'] = 'user_group';
$config['tables']['group_permissions'] = 'group_permission';
$config['tables']['logins'] = 'user_login';
$config['tables']['permissions'] = 'permission';
$config['tables']['login_attempts'] = 'login_attempt';

/*
| -------------------------------------------------------------------------
| Joins
| -------------------------------------------------------------------------
| Column to join with.
*/
$config['join']['users'] = 'user_id';
$config['join']['groups'] = 'group_id';
$config['join']['permissions'] = 'permission_id';


/*
| -------------------------------------------------------------------------
| Identity
| -------------------------------------------------------------------------
| You can use any unique column in your table as identity column. 
| The values in this column, alongside password, will be used for login purposes
*/
$config['identity_column'] = 'username';

/*
 | -------------------------------------------------------------------------
 | User Default Status (DEPRECATED)
 | -------------------------------------------------------------------------
 |
 | 1 = Active User
 | 0 = Nonactive User
 | -1 = Banned User
 */
//$config['user_default_status'] = 0;

/*
 | -------------------------------------------------------------------------
 | User Default Group
 | -------------------------------------------------------------------------
 | User will be automatically joined the group when they are registering
 | or when admin reset the user's permissions
 | 
 */
$config['user_default_group'] = 2;

/*
 | -------------------------------------------------------------------------
 | Activation Method
 | -------------------------------------------------------------------------
 |
 | manual = Only admin will activate new user
 | email  = New user will activated by email with verification link/code
 | FALSE   = No activation (Auto active)
 |
 */
$config['activation_method'] = 'manual';

/*
 | -------------------------------------------------------------------------
 | Protected Users
 | -------------------------------------------------------------------------
 | This configuration will protect users being deleted
 | by Smartc_auth::user_delete() method
 |
 | 'protected_users' = array of user identity that want to protect 
 |
 */
$config['protected_users'] = array('admin');

/*
 | -------------------------------------------------------------------------
 | Cookies.
 | -------------------------------------------------------------------------
 | Cookies Authentication name.
 |
 | 'identity'           = Cookie for user identity
 | 'token'              = Cookie for token sha256 based
 | 'token_identifier'   = Cookie for token identifier. It contains 32 alpha-numeric random string
 | 'expiration'         = Default expiration time in seconds
 | 'remember_expiration'= Expiration time in seconds when 'remember me' is checked
 |
 */
$config['cookies']['identity'] = 'user';
$config['cookies']['token'] = 'token';
$config['cookies']['token_identifier'] = 'token_identifier';
$config['cookies']['login_id'] = 'login_id';
$config['cookies']['expiration'] = 7200;
$config['cookies']['remember_expiration'] = 31536000;

/*
 | -------------------------------------------------------------------------
 | Login recheck
 | -------------------------------------------------------------------------
 | Interval time to check and update current session
 |
 | 'login_recheck' = Interval check time in seconds.
 |
 */
$config['login_recheck'] = 600;

/* 
 | -------------------------------------------------------------------------
 | Google reCAPTCHA keys
 | -------------------------------------------------------------------------
*/
$config['grecaptcha']['public_key'] = 'YOUR KEY HERE';
$config['grecaptcha']['secret_key'] = 'YOUR KEY HERE';

/*
 | -------------------------------------------------------------------------
 | JWT configs
 | -------------------------------------------------------------------------
*/
$config['jwt_key'] = 'YOUR KEY HERE';
$config['jwt_alg'] = 'HS256';

/*
 | -------------------------------------------------------------------------
 | Authentication configs
 | -------------------------------------------------------------------------
 */
$config['salt_type'] = PASSWORD_BCRYPT; // PHP Password constant algo

$config['track_login_attempts'] = TRUE;
$config['max_login_attempts'] = 3; // Max login attempts
$config['lockout_time'] = 100; // Lockout time in seconds

/*
 | -------------------------------------------------------------------------
 | Misc options
 | -------------------------------------------------------------------------
 */
$config['username_min_length'] = 4; // Set to 0 for no minimal length
$config['username_max_length'] = 32; // Set to 0 for no maximal length
$config['username_allowed_chars'] = 'a-z0-9_'; // Regular expression for allowed characters
$config['password_min_length'] = 4;
$config['password_max_length'] = 32; // Set to 0 for no maximal length

$config['message_start_delimeter'] = '';
$config['message_end_delimeter'] = '';
$config['message_new_line'] = "\n";

$config['auth_language'] = 'english';

$config['email_config'] = array(
    'protocol'  => 'smtp',
    'smtp_host' => 'ssl://smtp.googlemail.com',
    'smtp_port' => '',
    'smtp_user' => 'your_email@gmail.com', // change it to yours
    'smtp_pass' => 'yourpasshere', // change it to yours
    'mailtype'  => 'html',
    'charset'   => 'iso-8859-1',
    'wordwrap'  => TRUE
);
$config['email_subject'] = 'Smartc Auth';
$config['admin_email'] = 'noreply@youremail.com';


/*
 | -------------------------------------------------------------------------
 | Email templates.
 | -------------------------------------------------------------------------
 | Folder where email templates are stored.
 */
$config['email_templates'] = 'auth/email/';
$config['email_activate'] = 'activate.php';
