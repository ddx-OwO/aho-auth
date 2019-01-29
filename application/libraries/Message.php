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

class Message {

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

    public function __construct()
    {
        // Load the configuration file
        $this->config->load('aho_config', TRUE);

        // Get the language
        $language = $this->config->item('aho_lang', 'aho_config');
        if ($language === NULL)
        {
            $language = 'indonesian';
        }

        // Load the language file
        $this->lang->load('aho', $language, FALSE, TRUE, __DIR__.'/../');

        $this->message_start_delimeter = $this->config->item('message_start_delimeter', 'aho_config');
        $this->message_end_delimeter = $this->config->item('message_end_delimeter', 'aho_config');
        $this->message_new_line = $this->config->item('message_new_line', 'aho_config');
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

    public function message_string()
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
}