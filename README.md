# CodeIgniter WebSocket Library
CodeIgniter library realtime communication by using Websocket technology and Ratchet ([Socketo.me](http://socketo.me) & [ratchet_client](https://github.com/romainrg/ratchet_client))

If you Face any problem you may check CodeIgniter WebSocket Example https://github.com/takielias/codeigniter-websocket-example

## :books: Dependencies

- PHP 5.6+
- CodeIgniter Framework (3.1.* recommanded)
- Composer
- PHP sockets extension enabled

## :beginner: Installation

### :arrow_right: Step 1 : Library installation by Composer

Just by running following command in the folder of your project :
```sh
composer require takielias/codeigniter-websocket
```
Don't forget to include your autoload to CI config file :
```php
$config['composer_autoload'] = FCPATH.'vendor/autoload.php';
```
### :arrow_right: Step 2 : One command Setup

If you want Single command installation just Execute the Command in the Project directory

**N.B:** It will make 2 new controllers  Welcome.php and User.php
```sh
php vendor/takielias/codeigniter-websocket/install.php --app_path=application
```
Here app_path defines your default Codeigniter Application directory Name

![one click installation](https://user-images.githubusercontent.com/38932580/57182660-74df9a80-6ec3-11e9-8b31-37f3fcbf4ccd.png)

**WOW You made it !!!** :heavy_check_mark: 

Open two pages of your project on following url with different IDs :

`http://localhost/your project directory/index.php/user/index/1`

`http://localhost/your project directory/index.php/user/index/2`

:heavy_exclamation_mark: In this example, **recipient_id** is defined by **user_id**, as you can see, it's the **auth callback** who defines recipient ids.

If you have something like that, everything is ok for you:

![user_1](https://user-images.githubusercontent.com/38932580/57090224-21851500-6d28-11e9-9321-20d02e146d62.png)


![user_2](https://user-images.githubusercontent.com/38932580/57090269-44afc480-6d28-11e9-8ea1-30079a3a47e9.png)

You can try typing and sending something in each page (see cmd for more logs).

![cmd](https://user-images.githubusercontent.com/38932580/57090313-5abd8500-6d28-11e9-8644-8e0323a36a41.png)


#### :arrow_right: Run the Websocket server Manually
If you want to enable debug mode type the command bellow in you'r project folder :
```sh
php index.php welcome index
```
If you see the message the message bellow,  you are done (don't close your cmd) !

![First_launch.png](https://user-images.githubusercontent.com/14097222/40981263-d568413a-68da-11e8-9ab2-7b3f7224526e.PNG)
#### :arrow_right: Test the App

## Broadcast messages with your php App :boom: !
If you want to broadcast message with php script or something else you can use library like [textalk/websocket](https://github.com/Textalk/websocket-php) ***(who is included in my composer.json as required library)***

> *Note : The first message is mandatory and always here to perform authentication*

```php
$client = new Client('ws://0.0.0.0:8282');

$client->send(json_encode(array('user_id' => 1, 'message' => null)));
$client->send(json_encode(array('user_id' => 1, 'message' => 'Super cool message to myself!')));
```
## Authentication & callbacks :recycle:
The library allow you to define some callbacks, here's an example :
```php
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
        // However, method must return integer (client ID) if auth succedeed and false if not.
        return (!empty($datas->user_id)) ? $datas->user_id : false;
    }

    public function _event($datas = null)
    {
        // Here you can do everyting you want, each time message is received
        echo 'Hey ! I\'m an EVENT callback'.PHP_EOL;
    }
}
```

 - **Auth** type callback is called at first message posted from client.
 - **Event** type callback is called on every message posted.

## Bugs :bug: or feature :muscle:
Be free to open an issue or send pull request

## Support on Beerpay
Hey dude! Help me out for a couple of :beers:!

[![Beerpay](https://beerpay.io/takielias/codeigniter-websocket/badge.svg?style=beer-square)](https://beerpay.io/takielias/codeigniter-websocket)  [![Beerpay](https://beerpay.io/takielias/codeigniter-websocket/make-wish.svg?style=flat-square)](https://beerpay.io/takielias/codeigniter-websocket?focus=wish)