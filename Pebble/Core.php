<?php
require_once 'Pebble/Http.php';
require_once 'Pebble/Dispatcher.php';
class Pebble_Core
{
    protected static $_workingDir;
    protected static $_options = array("listen_port"     => 8383,
                                       "bind_address"    => '127.0.0.1',
                                       "request_handler" => 'serial',
                                       "document_root"   => ".");
    protected static $_dispatcher;
    
    public static function init($argv)
    {
        $dispatcherFilename = $argv[1];
        $dispatcherClassname = str_replace('.php', '', $dispatcherFilename);
        require_once($dispatcherFilename);
        self::$_dispatcher = new $dispatcherClassname;
        return true;
    }
    
    public static function getOptions()
    {
        return self::$_options;
    }
    
    
    public static function serve()
    {
        $options = self::$_options;
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($sock, $options['bind_address'], $options['listen_port']);
        socket_listen($sock);
        while (true)
        {
            $childSocket = socket_accept($sock);
            $input = socket_read($childSocket, 1024);
            if ($input) {
                $headers = Pebble_Http::parseRequestHeaders($input);
                $dispatcher = self::$_dispatcher;
                $response = $dispatcher->dispatch($headers['request']['uri']);
                echo $response;
                socket_write($childSocket, $response);
                socket_close($childSocket);
            } else {
                echo "Socket Error: " . socket_last_error($childSocket) . "\n";
            }
        }
        socket_close($sock);
    }
}
