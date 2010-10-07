<?php
class Pebble_Http
{
    const HTTP_NEWLINE = "\r\n";
    const HTTP_STATUS_200 = '200 OK';
    const HTTP_STATUS_404 = '404 Not Found';
    
    protected static $_defaultHeaders = array('Connection' => 'close');
    
    
    public static function parseRequestHeaders($rawHeaders)
    {
        $lines = explode(self::HTTP_NEWLINE, trim($rawHeaders));
        $rawRequest = array_shift($lines);
        $requestParts = explode(' ', $rawRequest);
        $request['method'] = $requestParts[0];
        $request['uri'] = $requestParts[1];
        
        foreach ($lines as $line) {
            $parts = explode(':', $line);
            list($name, $value) = $parts;
            $headers[$name] = $value;
        }
        $res['request'] = $request;
        $res['headers'] = $headers;
        return $res;
    }
    
    public static function formatResponseHeaders($status, $headers = array())
    {
        $response = 'HTTP/1.0 ' . $status . self::HTTP_NEWLINE;
        $headers = array_merge($headers, self::$_defaultHeaders);
        foreach ($headers as $key => $val) {
            $stringHeader = "$key: $val" . self::HTTP_NEWLINE;
            $response .= $stringHeader;
        }
        $response .= self::HTTP_NEWLINE . self::HTTP_NEWLINE;
        return $response;
    }
}