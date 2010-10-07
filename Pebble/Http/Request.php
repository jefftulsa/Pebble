<?php
class Pebble_Http_Request
{
    const HTTP_NEWLINE = "\r\n";
    protected $_method;
    protected $_uri;
    protected $_headers;
    
    public function __construct($rawRequest)
    {
        $headers = self::parseRequestHeaders($rawRequest);
        $this->_uri = $headers['request']['uri'];
        $this->_method = $headers['request']['method'];
        $this->_headers = $headers['headers'];
    }
    
    public function getHeaders()
    {
        return $this->_headers;
    }
    
    public function getUri()
    {
        return $this->_uri;
    }
    
    public function getMethod()
    {
        return $this->_method;
    }
    
    public function __toString()
    {
        return $this->_method . " " . $this->_uri;
    }
    
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
}