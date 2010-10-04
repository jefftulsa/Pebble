<?php
require_once 'Pebble/Http.php';
class Pebble_Core
{
    protected static $_workingDir;
    protected static $_options = array("listen_port"     => 8383,
                                       "bind_address"    => '127.0.0.1',
                                       "request_handler" => 'serial');
    
    public static function init($cwd, $ini)
    {
        self::$_workingDir = $cwd;
        $options = parse_ini_file($ini);
        foreach ($options as $key => $val) {
            self::$_options[$key] = $val;
        }
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
                echo $headers['request']['uri'] . PHP_EOL;
                $response = Pebble_Http::formatResponseHeaders(Pebble_Http::HTTP_STATUS_200);
                $requestHandlerClassname = 'Pebble_Handler_' . ucfirst($options['request_handler']);
                switch ($options['request_handler']) {
                    case 'serial':
                        $response = Pebble_Handler_Serial::handleRequest($headers);
                        break;
                }
                socket_write($childSocket, $response);
                socket_close($childSocket);
            } else {
                echo "Socket Error: " . socket_last_error($childSocket) . "\n";
            }
            echo memory_get_usage(true) . PHP_EOL;

        }
        socket_close($sock);
    }
}
