<?php
class Pebble_Http_Response
{
    const HTTP_STATUS_500 = 500;
    const HTTP_STATUS_200 = 200;
    const HTTP_STATUS_404 = 404;
    
    const HTTP_MSG_500 = 'Server Error';
    const HTTP_MSG_404 = 'Not Found';
    const HTTP_MSG_200 = 'Ok';
    
    const HTTP_NEWLINE = "\r\n";
    
    protected $_defaultHeaders = array('Connection' => 'close',
                                       'Server'     => 'Pebble');
    protected $_headers = array();
    protected $_body;
    protected $_statusCode;
        
    public function setBody($body)
    {
        $this->_body = $body;
    }
    
    public function setStatusCode($code)
    {
        $this->_statusCode = $code;
    }
    
    public function getStatusCode()
    {
        return $this->_statusCode;
    }
    
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }
    
    public function setHeader($name, $value)
    {
        $this->_headers[$name] = $value;
    }
    
    public function __toString()
    {
        $statusMessageConstant = 'HTTP_MSG_' . $this->_statusCode;
        $statusMessage = constant("self::$statusMessageConstant");
        
        $contentLength = strlen($this->_body);
        $this->_headers['Content-length'] = $contentLength;
        
        $response = 'HTTP/1.1 ' . $this->_statusCode . " " . $statusMessage . self::HTTP_NEWLINE;
        $headers = array_merge($this->_headers, $this->_defaultHeaders);
        foreach ($headers as $key => $val) {
            $stringHeader = "$key: $val" . self::HTTP_NEWLINE;
            $response .= $stringHeader;
        }
        $response .= self::HTTP_NEWLINE;
        $response .= $this->_body;
        return $response;
    }
}