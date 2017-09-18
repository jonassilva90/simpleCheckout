<?php

namespace SimpleCheckout;

class Session
{
   public function __construct(){
       if(session_status()!=PHP_SESSION_ACTIVE)
           session_start();
       
       if(!isset($_SESSION['cart'])){
           $_SESSION['cart'] = [
               'data' => [],
               'itens' => []
           ];
       }
   }
   public function get($name = null,$default = null){
       $value = $_SESSION['cart']['data'];
       if(is_null($name)){
           return $value;
       } else {
           $names = explode('.',$name);
           $found = false;
           foreach ($names as $name) {
               if(is_array($value) && isset($value[$name])){
                   $value = $value[$name];
                   $found = true;
               } else {
                   $found = false;
                   break;
               }
           }
           
           if(!$found){
               return $default;
           } else {
               return $value;
           }
       }
   }
   public function getItens($index = null,$name = null){
       if(is_null($index))
            return $_SESSION['cart']['itens'];
       
        if(isset($_SESSION['cart']['itens'][$index])){
            if(is_null( $_SESSION['cart']['itens'][$index] ))
                return $_SESSION['cart']['itens'][$index];
            elseif(isset($_SESSION['cart']['itens'][$index][$name]))
                return $_SESSION['cart']['itens'][$index][$name];
            else 
                return false;
        } else {
            return false;
        }
   }
   /** 
    * 
    * @param string $name
    * @param string $value
    * @param string $patternReplace
    * @return boolean
    */
   public function put($name, $value = null,$patternReplace = null){
       if(is_array($name)){
           $_SESSION['cart']['data'] = $name;
           return true;
       }
       
       if(!is_null($patternReplace) && !is_array($value) && !empty($value) )
           $value = preg_replace($patternReplace, '', $value );
       
       $names = explode('.',$name);
       
       return $this->_put($_SESSION['cart']['data'], $names, $value);
       
   }
   private function _put(&$var, $names,$value){
       if(count($names)>1){
           $name = array_shift($names);
           
           if(!isset($var[$name]))
               $var[$name] = [];
           
           return $this->_put($var[$name], $names, $value);
       } else {
           $var[ $names[0] ] = $value;
           return true;
       }
   }
   public function putItem($data,$index = null){
       if(is_null($index)){
           $_SESSION['cart']['itens'][] = $data;
           return true;
       } elseif(isset($_SESSION['cart']['itens'][$index])){
            $_SESSION['cart']['itens'][$index] = $data;
            return true;
       } else { 
           return false;
       }
   }
   public function putItens($data){
       $_SESSION['cart']['itens'] = $data;
       return true;
   }
   public function deleteItem($index){
       if(isset($_SESSION['cart']['itens'][$index]))
           unset($_SESSION['cart']['itens'][$index]);
       array_values($_SESSION['cart']['itens']);
       return true;
   }
   static function getSession($name = null,$default = null){
      $session = new Session();

      if(is_null($name))
            return $session;
      
      return $session->get($name, $default);
   }
   
}
