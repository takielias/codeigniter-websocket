<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ratchet Websocket Library: config file
 * @author Romain GALLIEN <romaingallien.rg@gmail.com>
 * @var array
 */
$config['codeigniter_websocket'] = array(
    'host' => '0.0.0.0',
    'port' => 8282,
    'timer_enabled' => false,
    'timer_interval' => 1, //1 means 1 seconds
    'auth' => true,
    'debug' => true
);
