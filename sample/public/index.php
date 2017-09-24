<?php 
error_reporting(E_ALL);
require_once realpath(__DIR__."/../../vendor")."/autoload.php";
require_once realpath(__DIR__."/../../src")."/Cart.php";
require_once realpath(__DIR__."/../../src")."/Session.php";
require_once realpath(__DIR__."/../../src/Database")."/Build.php";
require_once realpath(__DIR__."/../../src/Database")."/Model.php";
require_once realpath(__DIR__."/../../src/Database")."/Query.php";
require_once realpath(__DIR__."/../../src")."/Product.php";

function Router($request, $controller){
    $r = explode('/', $request);
    $rUrl = explode('/', $_SERVER['REQUEST_URI']);
    
    $p = [];
    $pattern = '/^\{([^?]+)([?])?\}$/';
    foreach ($r as $i=>$v){
        preg_match_all($pattern, $v, $matches, PREG_SET_ORDER, 0);
        if(is_null($matches) || empty($matches)){
            if(!isset($rUrl[$i]) || $v!=$rUrl[$i])
                return false;
        } else {
            $var = $matches[0][1];
            $require = !(isset($matches[0][2]) && $matches[0][2]=='?');
            
            if($require){
                if(isset($rUrl[$i])){
                    $p[] = $rUrl[$i];
                } else {
                    return false;
                }
            } else {
                if(isset($rUrl[$i])){
                    $p[] = $rUrl[$i];
                } else { 
                    $p[] = null;
                }
            }
        }
    }

    list($class,$method) = explode("@", $controller);
    $classFile = realpath(__DIR__.DIRECTORY_SEPARATOR."..").DIRECTORY_SEPARATOR.$class.".php";
    
    if(!is_file($classFile)){
        throw new \Exception("Arquivo não encontrado!FILE: ".$classFile);
    }
    
    require_once $classFile;
    $return = call_user_func_array(array(new $class, $method), $p);
    if($return===false){
        throw new \Exception("Erro inesperado");
    } else {
        if(is_array($return)){
            header("Content-type:text/json;charset=utf-8");
            echo json_encode($return);
        }
        
    }
    exit;
}

function view($view, $data = []){
    $dir = realpath(__DIR__.DIRECTORY_SEPARATOR."..");
    $filename = str_replace("/", DIRECTORY_SEPARATOR, $dir."/view/{$view}.php");
    
    if(is_file($filename)){
        extract($data);
        require_once $filename;
        return true;
    } else {
        throw new \Exception("Arquivo não encontrado!FILE: {$filename}");
        return false;
    }
}

try {
    \SimpleCheckout\Database\Query::setConnection('simplecheckot','localhost',3306,'jonas','jonas');
    Router('/api/cart/addItem/{idProduct}/{qtda?}', 'CartAPI@addItem');
    Router('/api/cart/setItem/{idProduct}/{qtda}/{action?}', 'CartAPI@setItem');
    Router('/api/cart/deleteItem/{idProduct}', 'CartAPI@deleteItem');
    Router('/api/cart/setAddress', 'CartAPI@setAddress');
    Router('/api/cart/setTypeShipping/{idtype}', 'CartAPI@setTypeShipping');
    
    Router('/', 'CartAPI@index');
    
    throw new \Exception("Rota não encotrada!");
} catch (\Exception $e) {
    echo "<h1>Erro</h1>";
    echo "<p>".$e->getMessage()."</p>";
    exit;
}
//include 'view/index.php';
?>