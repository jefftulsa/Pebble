<?php
require_once 'Pebble/Http/Request.php';
require_once 'Pebble/Http/Response.php';
require_once 'Pebble/Dispatcher.php';
require_once 'Pebble/Exception.php';
class Pebble_Core
{
    protected static $_options = array("listen_port"     => 8383,
                                       "bind_address"    => '127.0.0.1',
                                       "request_handler" => 'serial',
                                       "document_root"   => ".",
                                       "verbose"         => true);
    protected static $_dispatcher;
    protected static $_socket;
    protected static $_continue = true;
    
    public static function init($argv)
    {
        $dispatcherFilename = $argv[1];
        $dispatcherClassname = str_replace('.php', '', $dispatcherFilename);
        require_once($dispatcherFilename);
        self::$_dispatcher = new $dispatcherClassname();
        self::$_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        return true;
    }
    
    public static function getOptions()
    {
        return self::$_options;
    }
    
    public static function getPebblePath()
    {
        return realpath(dirname(__FILE__) . '/../');
    }
    
    public static function serve()
    {
        $options = self::$_options;
        $sock = self::$_socket;
        $bound = socket_bind($sock, $options['bind_address'], $options['listen_port']);
        if (!$bound) {
            return;
        }
        socket_listen($sock);
        echo "Server is listening on port " . $options['listen_port'] . PHP_EOL;
        while (self::$_continue)
        {
            $childSocket = socket_accept($sock);
            $input = socket_read($childSocket, 1024);
            if ($input) {
                $request = new Pebble_Http_Request($input);
                if ($options['verbose']) {
                    echo $request;
                }
                try {
                    $response = self::$_dispatcher->dispatch($request);                    
                } catch (Exception $e) {
                    echo "Uncaught Exception: " . $e->getMessage() . PHP_EOL;
                    $response = new Pebble_Http_Response(); 
                    $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_SERVER_ERROR);
                }
                if ($options['verbose']) {
                    echo " (" . $response->getStatusCode() . ")" . PHP_EOL;
                }
                socket_write($childSocket, $response->__toString());
                socket_close($childSocket);
                unset($childSocket);
            } else {
                echo "Socket Error: " . socket_last_error($childSocket) . "\n";
            }
        }
        socket_close(self::$_socket);
    }
    
    public static function shutdown()
    {
        self::$_continue = false;
    }
}
