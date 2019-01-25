<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/REST_Controller.php');
class Auth extends REST_Controller {

    public function index_post()
    {
        $this->response(['lalalala' => 'aaa']);
    }
}
