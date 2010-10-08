<?php
class Pebble_Dispatcher
{
    protected $_routes = array();
    
    public function __construct()
    {
        $rc = new ReflectionClass($this);
        $methods = $rc->getMethods();
        foreach ($methods as $method) {
            if (strpos($method->name, 'dispatch') === 0) {
                $comment = $method->getDocComment();
                if ($comment) {
                    $cleanComment = trim(str_replace(array("\t", "\r", "\n", "/*", "*/", "*"), '', $comment));
                    $parts = explode(' ', $cleanComment);
                    if ($parts[0] === '@dispatch') {
                        $this->_routes[$parts[1]] = $method->name;
                    }    
                }
            }
        }    
    }
    
    public function dispatch(Pebble_Http_Request $request)
    {
        $response = new Pebble_Http_Response();
        $urlParts = parse_url($request->getUri());
        $path = $urlParts['path'];
        $query = $urlParts['query'];
        parse_str($query, $params);
             
        if (isset($this->_routes[$path])) {
            $methodName =  $this->_routes[$path];
            $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_200);
            $response->setBody($this->$methodName($request, $response));
            return $response;
        } else if (file_exists(getcwd() . $request->getUri())) {
            $filename = getcwd() . $request->getUri();
            if (is_file($filename)) {
                $file = file_get_contents($file);
                $response->setBody($file);
                $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_200);                
            } else {
                $this->_404($response);
            }

        } else if (file_exists(Pebble_Core::getPebblePath() . '/resources' . $request->getUri())) {        
            $filename = Pebble_Core::getPebblePath() . '/resources' . $request->getUri();
            if (!is_file($filename)) {
                $this->_404($response);
            } else {
                $file = file_get_contents($filename);
                $response->setBody($file);
                $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_200);
            }
        } else {
            $this->_404($response);
        }
        return $response;
    }
    
    protected function _404($response)
    {
        $response->setBody(file_get_contents(Pebble_Core::getPebblePath() . '/resources/pebble-notfound.phtml'));
        $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_404);
        return $response;
    }
}