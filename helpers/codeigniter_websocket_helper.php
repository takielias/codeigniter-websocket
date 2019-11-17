<?php
/**
 * Author: takielias
 * Github Repo : https://github.com/takielias/codeigniter-websocket
 * Date: 04/05/2019
 * Time: 09:04 PM
 */

/**
 * Inspired By
 * Ratchet Websocket Library: helper file
 * @author Romain GALLIEN <romaingallien.rg@gmail.com>
 */

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('valid_json')) {

	/**
	 * Check JSON validity
	 * @method valid_json
	 * @param mixed $var Variable to check
	 * @return bool
	 */
	function valid_json($var)
	{
		return (is_string($var)) && (is_array(json_decode($var,
			true))) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
}

if (!function_exists('valid_jwt')) {

	/**
	 * Check JWT validity
	 * @method valid_jwt
	 * @param mixed $token Variable to check
	 * @return Object/false
	 */
	function valid_jwt($token)
	{
		return AUTHORIZATION::validateToken($token);
	}
}

/**
 * Codeigniter Websocket Library: helper file
 */
if (!function_exists('output')) {

	/**
	 * Output valid or invalid logs
	 * @method output
	 * @param string $type Log type
	 * @param string $var String
	 * @return string
	 */
	function output($type = 'success', $output = null)
	{
		if ($type == 'success') {
			echo "\033[32m" . $output . "\033[0m" . PHP_EOL;
		} elseif ($type == 'error') {
			echo "\033[31m" . $output . "\033[0m" . PHP_EOL;
		} elseif ($type == 'fatal') {
			echo "\033[31m" . $output . "\033[0m" . PHP_EOL;
			exit(EXIT_ERROR);
		} else {
			echo $output . PHP_EOL;
		}
	}
}
