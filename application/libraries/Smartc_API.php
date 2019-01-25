<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/REST_Controller.php');

class Smartc_API extends REST_Controller {

    protected $responses = array();

    protected static $http_codes = [
        100 => 'Continue',
        101 => 'Switching Protocols',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        511 => 'Network Authentication Required',
    ];

    protected static $error_codes = [
        'UDR-002' => 'data_not_found',
        'ULT-095' => 'unauthorized'
    ];

    const DEFAULT_LIMIT = 50;

    public function __construct($config = 'rest')
    {
        parent::__construct($config = 'rest');

        $this->load->library(['smartc_auth']);

        header('Access-Control-Allow-Credentials: true');
    }

    /**
     * @param int
     * @return static
     */
    public function set_http_code($http_code = NULL)
    {
        if (isset($http_code))
        {
            $this->responses['code'] = $http_code;
        }
        else
        {
            $this->responses['code'] = REST_Controller::HTTP_OK;
        }

        if (isset(self::$http_codes[$http_code]))
        {
            $this->responses['status'] = self::$http_codes[$http_code];
        }
        else
        {
            $this->responses['status'] = $http_code;
        }

        return $this;
    }

    /**
     * Prepare response format
     * 
     * @param array $data
     * @param int $http_code
     * @param array|NULL $extra
     * @return array
     */
    public function prepare_response($data, $http_code = NULL, $extra = NULL)
    {
        if(isset($this->responses['code']))
        {
            $http_code = $this->responses['code'];
        }

        $this->set_http_code($http_code);

        $this->responses['data'] = $data;
        if (isset($extra))
        {
            $this->responses = array_merge($this->responses, $extra);
        }

        return $this->responses;
    }

    public function prepare_error_response($type, $message, $http_code = NULL, $extra = NULL)
    {
        if(isset($this->responses['code']))
        {
            $http_code = $this->responses['code'];
        }

        $this->set_http_code($http_code);

        $this->responses['errors'] = [
            'type' => $type,
            'message' => $message,
        ];

        if (isset($extra) && ! empty($extra))
        {
            $this->responses = array_merge($this->responses, $extra);
        }

        return $this->responses;
    }

    
    public function get_table_columns($table)
    {
        return $this->db->list_fields($table);
    }

    public function get_limit()
    {
        $limit = REST_Controller::get('limit', TRUE) ? REST_Controller::get('limit', TRUE) : Smartc_API::DEFAULT_LIMIT;
        
        if (preg_match('/^[0-9]+$/i', $limit))
        {
            return $limit;
        }

        return self::DEFAULT_LIMIT;
    }

    public function get_offset()
    {
        $offset = REST_Controller::get('offset', TRUE);
        
        if (preg_match('/^[0-9]+$/i', $offset))
        {
            return $offset;
        }

        return NULL;
    }

    /**
     * @return void
     */
    protected function _check_session()
    {
        if ($this->smartc_auth->is_logged_in() === FALSE)
        {
            $res = $this->set_http_code(REST_Controller::HTTP_UNAUTHORIZED)
                        ->prepare_error_response('unauthorized', 'Invalid Login');

            REST_Controller::response($res, $res['code']);
        }
    }

    protected function _check_authority($level)
    {
        
    }
}
