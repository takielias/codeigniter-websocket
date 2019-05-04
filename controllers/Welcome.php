<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{

    public function index()
    {
        // Load package path
        $this->load->add_package_path(FCPATH . 'vendor/takielias/codeigniter-websocket');
        $this->load->library('Codeigniter_websocket');
        $this->load->remove_package_path(FCPATH . 'vendor/takielias/codeigniter-websocket');

        // Run server
        $this->codeigniter_websocket->set_callback('auth', array($this, '_auth'));
        $this->codeigniter_websocket->set_callback('event', array($this, '_event'));
        $this->codeigniter_websocket->run();
    }


    public function _auth($datas = null)
    {
        // Here you can verify everything you want to perform user login.

        return (!empty($datas->user_id)) ? $datas->user_id : false;
    }

    public function _event($datas = null)
    {
        // Here you can do everyting you want, each time message is received
        echo 'Hey ! I\'m an EVENT callback' . PHP_EOL;
    }

}
