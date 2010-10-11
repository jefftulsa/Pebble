<?php
class Pebble_Dispatcher
{
    protected $_routes = array();
    
    public function __construct()
    {
         $this->_registerRoutes();
    }
    
    protected function _registerRoutes()
    {
        $methods = get_class_methods(get_class($this));
        foreach ($methods as $method) {
            if (substr($method, 0, 3) === '___') {
                $routeName = substr($method, 3);
                $routeName = str_replace('_', '-', $routeName);
                $this->_routes[$routeName] = $method;
            }
        }
    }
    
    protected function _getRoute($request)
    {
        $url = $request->getUrl();
        $bareUrl = str_replace('/', '', $url);
        if (isset($this->_routes[$bareUrl])) {
            return $this->_routes[$bareUrl];
        }
        return false;
    }
    
    public function dispatch(Pebble_Http_Request $request)
    {
        $response = new Pebble_Http_Response();
             
        if ($methodName = $this->_getRoute($request)) {
            $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_200);
            try {
                ob_start();            
                $statusCode = $this->$methodName($request);
                $responseBody = ob_get_contents();
                ob_end_clean();            
                $response->setBody($responseBody);
                if ($statusCode !== null) {
                    $response->setStatusCode($statusCode);
                }
            } catch (Exception $e) {
                $this->_500($response, $e);
            }
            return $response;
        } else if (file_exists(getcwd() . $request->getUrl())) {
            $filename = getcwd() . $request->getUrl();
            if (is_file($filename)) {
                $file = file_get_contents($file);
                $response->setBody($file);
                $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_200);                
            } else {
                $this->_404($response);
            }

        } else if (file_exists(PEBBLE_ROOT . '/resources' . $request->getUrl())) {        
            $filename = PEBBLE_ROOT . '/resources' . $request->getUrl();
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
        $response->setBody(file_get_contents(PEBBLE_ROOT . '/resources/pebble-notfound.phtml'));
        $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_404);
        return $response;
    }
    
    protected function _500($response, $e)
    {
        ob_end_clean();
        $traces = debug_backtrace(true);
        print_r($traces);
        array_shift($traces); 

        ob_start();
        include PEBBLE_ROOT . '/resources/pebble-error.phtml';
        $output = ob_get_contents();
        ob_end_clean();
        $response->setStatusCode(Pebble_Http_Response::HTTP_STATUS_500);
        $response->setBody($output);
    }
}