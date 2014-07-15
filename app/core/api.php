<?php
class Api
{
    protected $_request       = null;
    protected $_response      = null;
    protected $_response_code = 200;
    
    public $jsonp      = false;
    public $loaded     = false;
    public $authorized = false;

    public function __destruct()
    {
        if ($this->loaded) {
            $this->present();
        }
    }
    
    public function authorizeToken($request = array())
    {
        
        $headers = apache_request_headers();
        foreach ($headers as $header => $value) {
            if ($header == 'Authorization') {
                $auth_token = str_replace('Bearer: ', '', $value);
            }
        }
        
        if (!empty($auth_token)) {
            $token = Model::get('oauth_tokens')->load(array('token' => $auth_token));
            if (!$token->isLoaded()) {
                $this->loaded = true;
                $this->setResponse( array(
                    "{$request['action']}" => array(
                        "success" => false,
                        "error_code" => "bad_auth_token",
                        "error"      => "Bad Auth Token.",
                        "errors"     => array("auth_token")
                    )
                ) );
                $this->setResponseCode(401);
                exit();
            }
        }
        else {
            $this->loaded = true;
            $this->setResponse( array(
                "{$request['action']}" => array(
                    "success" => false,
                    "error_code" => "no_auth_token",
                    "error"      => "No Auth Token.",
                    "errors"     => array("auth_token")
                )
            ) );
            exit();
        }
        
    }
    
    public function load($name = "", $request = array())
    {

        #$this->authorizeToken($request);
        
        $this->controller_name = $name . "_controller";
        $controller = Comet::camalizeClassName($this->controller_name);
                
        if (Comet::isAppFileByClass($controller)) {
            
            $controller = new $controller();
            $controller->loaded     = true;
            $controller->authorized = true;
            
            $request_body = $this->getRequestBody();
            if (!empty($request_body)) {
                $controller->setRequest(array_merge($request_body, $_REQUEST));    
                return $controller;
            }
            
            $controller->setRequest($request);
            return $controller;
        }
            
        return null;
    }
    
    public function getRequestBody()
    {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            return json_decode(file_get_contents('php://input'), true);
        }
        return null;
    }
    
    public function setRequest($request = array())
    {
        $this->_request = $request;
    }
    
    public function getRequest()
    {
        return $this->_request;
    }
    
    public function setResponse($response = array())
    {
        $this->_response = $response;
    }
    
    public function setResponseCode($code = 0)
    {
        $this->_response_code = $code;
    }
    
    public function getResponse()
    {
        return $this->_response;
    }
    
    public function getResponseCode()
    {
        return $this->_response_code;
    }
            
    public function present()
    {
        if ($this->jsonp) {
            header('Content-Type: text/javascript');
        } 
        else {
            header('Content-Type: application/json');
        }
    
        $response = $this->getResponse();
        if (empty($response)) {
            $this->setResponseCode(204);
        }
        
        http_response_code($this->getResponseCode());
        echo json_encode($response);
        exit();
    }
    
}