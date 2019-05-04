<?php
/**
 * Author: takielias
 * Github Repo : https://github.com/takielias/codeigniter-websocket
 * Date: 04/05/2019
 * Time: 09:04 PM
 */

if (strpos($argv[1], '--app_path=') !== false) {
    $app_path = explode('--app_path=', $argv[1])[1];
    copy('vendor/takielias/codeigniter-websocket/config/codeigniter_websocket.php', $app_path . '/config/codeigniter_websocket.php');
    $str = file_get_contents('vendor/takielias/codeigniter-websocket/config/jwt.php');
    $str = str_replace("%your JWT Private Key%", substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1, 32))), 1, 32), $str);
    file_put_contents($app_path . '/config/jwt.php', $str);
    copy('vendor/takielias/codeigniter-websocket/controllers/User.php', $app_path . '/controllers/User.php');
    copy('vendor/takielias/codeigniter-websocket/controllers/Welcome.php', $app_path . '/controllers/Welcome.php');
    copy('vendor/takielias/codeigniter-websocket/views/welcome_message.php', $app_path . '/views/welcome_message.php');
    $response = "Codeigniter WebSocket has been installed successfully !!!";
    echo "\033[32m " . $response . "\033[37m\r\n";
    echo "\033[32m Server is Running on port 8282 \033[37m\r\n";
    exec('php index.php welcome index');
} else {
    echo "\033[31mInvalid Command. Please check https://github.com/takielias/codeigniter-websocket\033[37m\r\n";
}

