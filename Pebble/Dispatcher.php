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
            $file = file_get_contents(getcwd() . $request->getUri());
            $response->setBody($file);
            $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_200);
        } else {
            $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_400);
        }
        return $response;
    }
    
    public function render($view)
    {
        ob_start();
        include(getcwd() . '/' . $view);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}