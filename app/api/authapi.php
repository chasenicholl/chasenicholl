<?php
class AuthApiController extends Api
{
    
    public function oauthAuthorize()
    {
        $request = $this->getRequest();
        $valid   = Utility::validateFields(
            $request, 
            array(
                'client_id'
            )
        );
                
        if (!$valid->success) {
            exit; 
        }
                
        $app = Model::get('apps')->load(
            array(
                'client_id' => $request['client_id']
            )
        );
                
        if ($app->isLoaded()) {
            
            $code = Apps::generateOauthCode($app);
            $this->setResponse(array(
                $request['action'] => array(
                    "code" => $code
                )
            ) );
            
        }
    
    }
    
    public function oauthAccessToken()
    {
        
        if (Comet::assertMethod('post')) {
        
            $request = $this->getRequest();    
            $valid   = Utility::validateFields(
                $request, 
                array(
                    'client_id', 
                    'client_secret',
                    'code'
                )
            );
            
            if (!$valid->success) {
                exit; 
            }
                        
            $app = Model::get('apps')->load(
                array(
                    'client_id'     => $request['client_id'],
                    'client_secret' => $request['client_secret']
                )
            );
                        
            if ($app->isLoaded()) {
                
                $token = Apps::exchangeCodeForToken($app, $request['code']);
                $this->setResponse(array(
                    $request['action'] => array(
                        "token" => $token
                    )
                ) );
                
            }
            
        }
        
    }
    
}