<?php
class OrdersApiController extends Api
{

    public function orders()
    {    
        $request    = $this->getRequest();
        $all_orders = Model::get('orders')->getAllOrders();
        $response   = array($request['action'] => $all_orders);
        $this->setResponse($response);
    }
    
}