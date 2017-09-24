<?php



use SimpleCheckout\Cart;

class CartAPI {
    public function index(){
        $cart = $this->cart();
        
        return view('index',['dataCart' => $cart->all()]);
    }
    
    /**
     * @return \SimpleCheckout\Cart
     */
    private function cart(){
        return new Cart(realpath(__DIR__."").'/correios.config');
    }
    private function returnApi(&$cart){
        return ['status' => true, 'strError'=>'','data' => $cart->all() ];
    }
    public function addItem($idProduct, $qtda = null){
        try {
            $cart = $this->cart();
            $cart->addItem((int)$idProduct, is_null($qtda)? 1 : (int)$qtda );
            return $this->returnApi($cart);
        } catch (\Exception $e) {
            return ['status' => false, 'strError'=>$e->getMessage(),'data' => null];
        }
        
    }
    public function setItem($idProduct, $qtda ,$action = null){
        try {
            $cart = $this->cart();
            $cart->setItem((int)$idProduct, (int)$qtda ,(int)$action);
            return $this->returnApi($cart);
        } catch (\Exception $e) {
            return ['status' => false, 'strError'=>$e->getMessage(),'data' => null];
        }
    }
    public function deleteItem($idProduct){
        try {
            $cart = $this->cart();
            $cart->deleteItem((int)$idProduct);
            return $this->returnApi($cart);
        } catch (\Exception $e) {
            return ['status' => false, 'strError'=>$e->getMessage(),'data' => null];
        }
    }
    public function setTypeShipping($typeShipping){
        try{
            $cart = $this->cart();
            $cart->set('type_shipping', $typeShipping);
            $cart->calcTotal(true);
            return $this->returnApi($cart);
        } catch (\Exception $e) {
            return ['status' => false, 'strError'=>$e->getMessage(),'data' => null];
        }
    }
    public function setAddress(){
        try {
            $cart = $this->cart();
            $cart->setAddress($_POST);
            return $this->returnApi($cart);
        } catch (\Exception $e) {
            return ['status' => false, 'strError'=>$e->getMessage(),'data' => null];
        }
    }
}