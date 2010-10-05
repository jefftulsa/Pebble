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
    
    public function dispatch($requestUri)
    {
        $urlParts = parse_url($requestUri);
        $path = $urlParts['path'];
        $query = $urlParts['query'];
        if (isset($this->_routes[$path])) {
            $methodName =  $this->_routes[$path];
            ob_start();
            $this->$methodName(parse_str($query));
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }
}