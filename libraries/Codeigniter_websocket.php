<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Namespaces
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * @package   CodeIgniter Ratchet WebSocket Library: Main class
 * @category  Libraries
 * @author    Taki Elias <taki.elias@gmail.com>
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://github.com/takielias
 *
 * CodeIgniter WebSocket library. It allows you to make powerfull realtime applications by using Ratchet Websocket technology
 */

/**
 * Inspired By
 * Ratchet Websocket Library: helper file
 * @author Romain GALLIEN <romaingallien.rg@gmail.com>
 */

class Codeigniter_websocket
{
	/**
	 * CI Super Instance
	 * @var array
	 */
	private $CI;

	/**
	 * Default host var
	 * @var string
	 */
	public $host = null;

	/**
	 * Default host var
	 * @var string
	 */
	public $port = null;

	/**
	 * Default auth var
	 * @var bool
	 */
	public $auth = false;

	/**
	 * Default Timer Interval var
	 * @var bool
	 */
	public $timer_interval = 1;

	/**
	 * Default debug var
	 * @var bool
	 */
	public $debug = false;

	/**
	 * Auth callback informations
	 * @var array
	 */
	public $callback = array();

	/**
	 * Config vars
	 * @var array
	 */
	protected $config = array();

	/**
	 * Define allowed callbacks
	 * @var array
	 */
	protected $callback_type = array('auth', 'event', 'close', 'citimer', 'roomjoin', 'roomleave', 'roomchat');

	/**
	 * Class Constructor
	 * @method __construct
	 * @param array $config Configuration
	 * @return void
	 */
	public function __construct(array $config = array())
	{
		// Load the CI instance
		$this->CI = &get_instance();

		// Load the class helper
		$this->CI->load->helper('codeigniter_websocket');
		$this->CI->load->helper('jwt');
		$this->CI->load->helper('authorization');

		// Define the config vars
		$this->config = (!empty($config)) ? $config : array();

		// Config file verification
		if (empty($this->config)) {
			output('fatal', 'The configuration file does not exist');
		}

		// Assign HOST value to class var
		$this->host = (!empty($this->config['codeigniter_websocket']['host'])) ? $this->config['codeigniter_websocket']['host'] : '';

		// Assign PORT value to class var
		$this->port = (!empty($this->config['codeigniter_websocket']['port'])) ? $this->config['codeigniter_websocket']['port'] : '';

		// Assign AUTH value to class var
		$this->auth = (!empty($this->config['codeigniter_websocket']['auth'] && $this->config['codeigniter_websocket']['auth'])) ? true : false;

		// Assign DEBUG value to class var
		$this->debug = (!empty($this->config['codeigniter_websocket']['debug'] && $this->config['codeigniter_websocket']['debug'])) ? true : false;

		// Assign Timer value to class var
		$this->timer = (!empty($this->config['codeigniter_websocket']['timer_enabled'] && $this->config['codeigniter_websocket']['timer_enabled'])) ? true : false;

		// Assign Timer Interval value to class var
		$this->timer_interval = (!empty($this->config['codeigniter_websocket']['timer_interval'])) ? $this->config['codeigniter_websocket']['timer_interval'] : 1;
	}

	/**
	 * Launch the server
	 * @method run
	 * @return string
	 */
	public function run()
	{
		// Initiliaze all the necessary class
		$server = IoServer::factory(
			new HttpServer(
				new WsServer(
					new Server()
				)
			),
			$this->port,
			$this->host
		);

		//If you want to use timer
		if ($this->timer != false) {
			$server->loop->addPeriodicTimer($this->timer_interval, function () {
				if (!empty($this->callback['citimer'])) {
					call_user_func_array($this->callback['citimer'], array(date('d-m-Y h:i:s a', time())));
				}
			});

		}

		// Run the socket connection !
		$server->run();
	}

	/**
	 * Define a callback to use auth or event callback
	 * @method set_callback
	 * @param array $callback
	 * @return void
	 */
	public function set_callback($type = null, array $callback = array())
	{
		// Check if we have an authorized callback given
		if (!empty($type) && in_array($type, $this->callback_type)) {

			// Verify if the method does really exists
			if (is_callable($callback)) {

				// Register callback as class var
				$this->callback[$type] = $callback;
			} else {
				output('fatal', 'Method ' . $callback[1] . ' is not defined');
			}
		}
	}
}

/**
 * @package   CodeIgniter WebSocket Library: Server class
 * @category  Libraries
 * @author    Taki Elias <taki.elias@gmail.com>
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://github.com/takielias
 *
 * CodeIgniter WebSocket library. It allows you to make powerfull realtime applications by using Ratchet Websocket technology
 */
class Server implements MessageComponentInterface
{
	/**
	 * List of connected clients
	 * @var array
	 */
	public $clients;

	/**
	 * List of subscribers (associative array)
	 * @var array
	 */
	protected $subscribers = array();

	/**
	 * Class constructor
	 * @method __construct
	 */
	public function __construct()
	{
		// Load the CI instance
		$this->CI = &get_instance();

		// Initialize object as SplObjectStorage (see PHP doc)
		$this->clients = new SplObjectStorage;

		// // Check if auth is required
		if ($this->CI->codeigniter_websocket->auth && empty($this->CI->codeigniter_websocket->callback['auth'])) {
			output('fatal', 'Authentication callback is required, you must set it before run server, aborting..');
		}

		// Output
		if ($this->CI->codeigniter_websocket->debug) {
			output('success',
				'Running server on host ' . $this->CI->codeigniter_websocket->host . ':' . $this->CI->codeigniter_websocket->port);
		}

		// Output
		if (!empty($this->CI->codeigniter_websocket->callback['auth']) && $this->CI->codeigniter_websocket->debug) {
			output('success', 'Authentication activated');
		}

		// Output
		if (!empty($this->CI->codeigniter_websocket->callback['close']) && $this->CI->codeigniter_websocket->debug) {
			output('success', 'Close activated');
		}

	}

	/**
	 * Event trigerred on new client event connection
	 * @method onOpen
	 * @param ConnectionInterface $connection
	 * @return string
	 */
	public function onOpen(ConnectionInterface $connection)
	{
		// Add client to global clients object
		$this->clients->attach($connection);

		// Output
		if ($this->CI->codeigniter_websocket->debug) {
			output('info', 'New client connected as (' . $connection->resourceId . ')');
		}
	}

	/**
	 * Event trigerred on new message sent from client
	 * @method onMessage
	 * @param ConnectionInterface $client
	 * @param string $message
	 * @return string
	 */
	public function onMessage(ConnectionInterface $client, $message)
	{
		// Broadcast var
		$broadcast = false;

		// Check if received var is json format
		if (valid_json($message)) {
			// If true, we have to decode it
			$datas = json_decode($message);

			// Once we decoded it, we check look for global broadcast
			$broadcast = (!empty($datas->broadcast) and $datas->broadcast == true) ? true : false;

			// Count real clients numbers (-1 for server)
			$clients = count($this->clients) - 1;

			// Here we have to reassign the client ressource ID, this will allow us to send message to specified client.

			if (!empty($datas->type) && $datas->type == 'socket') {

				if (!empty($this->CI->codeigniter_websocket->callback['auth'])) {

					// Call user personnal callback
					$auth = call_user_func_array($this->CI->codeigniter_websocket->callback['auth'],
						array($datas));

					// Verify authentication

					if (empty($auth) or !is_integer($auth)) {
						output('error', 'Client (' . $client->resourceId . ') authentication failure');
						$client->send(json_encode(array("type" => "error", "msg" => 'Invalid ID or Password.')));
						// Closing client connexion with error code "CLOSE_ABNORMAL"
						$client->close(1006);
					}

					// Add UID to associative array of subscribers
					$client->subscriber_id = $auth;

					if ($this->CI->codeigniter_websocket->auth) {
						$data = json_encode(array("type" => "token", "token" => AUTHORIZATION::generateToken($client->resourceId)));
						$this->send_message($client, $data, $client);
					}

					// Output
					if ($this->CI->codeigniter_websocket->debug) {
						output('success', 'Client (' . $client->resourceId . ') authentication success');
						output('success', 'Token : ' . AUTHORIZATION::generateToken($client->resourceId));
					}
				}

			}


			if (!empty($datas->type) && $datas->type == 'roomjoin') {

				if (valid_jwt($datas->token) != false) {

					if (!empty($this->CI->codeigniter_websocket->callback['roomjoin'])) {

						// Call user personnal callback
						call_user_func_array($this->CI->codeigniter_websocket->callback['roomjoin'],
							array($datas, $client));

					}


				} else {

					$client->send(json_encode(array("type" => "error", "msg" => 'Invalid Token.')));
				}

			}

			if (!empty($datas->type) && $datas->type == 'roomleave') {

				if (valid_jwt($datas->token) != false) {

					if (!empty($this->CI->codeigniter_websocket->callback['roomleave'])) {

						// Call user personnal callback
						call_user_func_array($this->CI->codeigniter_websocket->callback['roomleave'],
							array($datas, $client));

					}


				} else {

					$client->send(json_encode(array("type" => "error", "msg" => 'Invalid Token.')));
				}

			}

			if (!empty($datas->type) && $datas->type == 'roomchat') {

				if (valid_jwt($datas->token) != false) {

					if (!empty($this->CI->codeigniter_websocket->callback['roomchat'])) {

						// Call user personnal callback
						call_user_func_array($this->CI->codeigniter_websocket->callback['roomchat'],
							array($datas, $client));

					}


				} else {

					$client->send(json_encode(array("type" => "error", "msg" => 'Invalid Token.')));
				}

			}


			// Now this is the management of messages destinations, at this moment, 4 possibilities :
			// 1 - Message is not an array OR message has no destination (broadcast to everybody except us)
			// 2 - Message is an array and have destination (broadcast to single user)
			// 3 - Message is an array and don't have specified destination (broadcast to everybody except us)
			// 4 - Message is an array and we wan't to broadcast to ourselves too (broadcast to everybody)

			if (!empty($datas->type) && $datas->type == 'chat') {

				$pass = true;

				if ($this->CI->codeigniter_websocket->auth) {

					if (!valid_jwt($datas->token)) {
						output('error', 'Client (' . $client->resourceId . ') authentication failure. Invalid Token');
						$client->send(json_encode(array("type" => "error", "msg" => 'Invalid Token.')));
						// Closing client connexion with error code "CLOSE_ABNORMAL"
						$client->close(1006);
						$pass = false;
					}
				}

				if ($pass) {
					if (!empty($message)) {

						// We look arround all clients
						foreach ($this->clients as $user) {

							// Broadcast to single user
							if (!empty($datas->recipient_id)) {
								if ($user->subscriber_id == $datas->recipient_id) {
									$this->send_message($user, $message, $client);
									break;
								}
							} else {
								// Broadcast to everybody
								if ($broadcast) {
									$this->send_message($user, $message, $client);
								} else {
									// Broadcast to everybody except us
									if ($client !== $user) {
										$this->send_message($user, $message, $client);
									}
								}
							}
						}
					}
				}

			}

		} else {
			output('error', 'Client (' . $client->resourceId . ') Invalid json.');
			// Closing client connexion with error code "CLOSE_ABNORMAL"
			$client->close(1006);
		}

	}

	/**
	 * Event triggered when connection is closed (or user disconnected)
	 * @method onClose
	 * @param ConnectionInterface $connection
	 * @return string
	 */
	public function onClose(ConnectionInterface $connection)
	{
		// Output
		if ($this->CI->codeigniter_websocket->debug) {
			output('info', 'Client (' . $connection->resourceId . ') disconnected');
		}

		if (!empty($this->CI->codeigniter_websocket->callback['close'])) {
			call_user_func_array($this->CI->codeigniter_websocket->callback['close'], array($connection));
		}
		// Detach client from SplObjectStorage
		$this->clients->detach($connection);
	}

	/**
	 * Event trigerred when error occured
	 * @method onError
	 * @param ConnectionInterface $connection
	 * @param Exception $e
	 * @return string
	 */
	public function onError(ConnectionInterface $connection, \Exception $e)
	{
		// Output
		if ($this->CI->codeigniter_websocket->debug) {
			output('fatal', 'An error has occurred: ' . $e->getMessage());
		}

		// We close this connection
		$connection->close();
	}

	/**
	 * Function to send the message
	 * @method send_message
	 * @param array $user User to send
	 * @param array $message Message
	 * @param array $client Sender
	 * @return string
	 */
	protected function send_message($user = array(), $message = array(), $client = array())
	{
		// Send the message
		$user->send($message);

		// We have to check if event callback must be called
		if (!empty($this->CI->codeigniter_websocket->callback['event'])) {

			// At this moment we have to check if we have authent callback defined
			call_user_func_array($this->CI->codeigniter_websocket->callback['event'],
				array((valid_json($message) ? json_decode($message) : $message)));

			// Output
			if ($this->CI->codeigniter_websocket->debug) {
				output('info', 'Callback event "' . $this->CI->codeigniter_websocket->callback['event'][1] . '" called');
			}
		}

		// Output
		if ($this->CI->codeigniter_websocket->debug) {
			output('info',
				'Client (' . $client->resourceId . ') send \'' . $message . '\' to (' . $user->resourceId . ')');
		}
	}

}
