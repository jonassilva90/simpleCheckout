<?php

namespace SimpleCheckout;

use Correios\Config;
use Correios\ECT;
class Cart
{
   const SHIPPING_SEDEX = 40010;//SEDEX Varejo
   const SHIPPING_SEDEX_A_COBRAR = 40045;//SEDEX a Cobrar Varejo
   const SHIPPING_SEDEX_10 = 40215;//SEDEX 10 Varejo
   const SHIPPING_SEDEX_HOJE = 40290;//SEDEX Hoje Varejo
   const SHIPPING_PAC = 41106;//PAC Varejo
    
   private $session;
   
   public function __construct($configFile){
       $this->session = Session::getSession();
       Config::setDefaults( $configFile );
       if(empty( $this->session->get() )){
           $this->session->put([
               'token_cupon' => '',//Token de desconto
               'type_shipping' => null,//Tipo do transporte
               'shipping' => [],//Calculos de frete e prazo para entrega
               'address' => [
                   'zipcode' => '',//CEP sem pontos e tracos
                   'street' => '',//Rua/Av ....
                   'number' => '',//Numero da casa
                   'compl' => '',//Complemento da casa(tipo AP 101)
                   'district' => '',//Bairro
                   'city' => '',//Cidade
                   'state' => ''//Estado
               ],
               'total' => [
                   'weight' => 0,//Peso em kg (Soma total)
                   'price'  => 0,//Preco (Soma total)
                   'width'  => 0,//Largura em cm (Max)
                   'height' => 0,//Altura em cm (Max)
                   'length' => 0//Comprimento em cm (Soma total)
               ]
           ]);
       }
   }
   public function all(){
       $r = [
            'itens' => $this->session->getItens(),
            'data' => $this->session->get()
       ];
       
       //Buscar FRETE e PRAZO
       if(!empty($r['data']['address']['zipcode']) && empty($r['data']['shipping']))
           $r['data']['shipping'] = $this->calcShipping();//FIXME Falta jonassilva/correios
       
       return $r;
   }
   /** Pagar dados do carrinho
    * 
    * @param string $name Pegar um atributo especifico OU NULL para retornar todos
    * @param unknown $default Valor se nao encontrar o atributo
    * @example Cart->get('address.zipcode'); ou Cart->get(); ou get('type_shipping'); 
    * 
    * @return unknown 
    */
   public function get($name = null,$default = null){
       return $this->session->get($name,$default);
   }
   public function getItens($index = null){
       return $this->session->getItens($index);
   }
   public function set($name,$value){
       $patternReplace = null;
       if(!is_array($name)){
           switch ($name){
               //ALFA-NUMERICO e =
               case 'token_cupon':
                   $patternReplace = '/[^A-Z0-9=]/';
                   break;
               //Numerico(sem ponto)
               case 'type_shipping':
               case 'address.zipcode':
                   $patternReplace = '/[^0-9]/';
                   break;
               default:
                   $patternReplace = null;
                   break;
           }
       }
       
       return $this->session->put($name,$value,$patternReplace);
   }
   public function setAddress($data){
       $p = $this->get('address');
       
       foreach ($p as $k=>$v){
           if(isset($data[$k]))
               $p[$k] = $data[$k];
       }
           
       $this->set('address', $data);
       $this->calcShipping();
   }
   /**
    * 
    * @param int idProduct Id do produto
    * @param int $amount Quantidade
    * @param string $action 0 - Alterar item (p.e.: q = newQ ) / 1 - Incrementar(qtda) item (p.e.: q += newQ)/ 2 - Decrementar(qtda) item(p.e.: q -= newQ) / 3 - Delete Item
    */
   public function setItem(int $id_product, int $amount = 1,int $action = 0){
       $dataProduct = Product::select('id','title','price','weight','width','height','length','digital')->find($id_product);
       if($dataProduct===false){
           throw new \Exception("Produto não encontrado");
       }
       
       $amountItem = 0;
       $index = null;
       foreach ($this->session->getItens() as $i=>$item){
           if($item['product']['id']==$id_product){
               $amountItem = $item['amount'];
               $index = $i;
           }
       }
       
       switch ($action){
           case 1://Increment
               $amountItem = $amountItem + $amount;
               break;
           case 2://Decrementar
               $amountItem = $amountItem - $amount;
               break;
           case 3://Delete item
               $amountItem = 0;
               break;
           default://Default
               $amountItem = $amount;
               break;
       }
       
      
       if($amountItem<=0){//Excluir item
           if( !is_null($index) )
                $this->session->deleteItem( $index );
       } else {//Salvar Item
           
           $dataItem = [
               'product' => $dataProduct,
               'amount' => $amountItem,
               'total' => [
                   'weight' => ($dataProduct['weight']/1000 * $amountItem),//Peso em kg (Soma total)
                   'price'  => ($dataProduct['price'] * $amountItem),//Preco (Soma total)
                   'width'  => ceil($dataProduct['width']/10),//Largura em cm (Max)
                   'height' => ceil($dataProduct['height']/10)*$amountItem,//Altura em cm (Max)
                   'length' => ceil($dataProduct['length']/10)//Comprimento em cm (Soma total)
               ]
           ];
           
           $this->session->putItem($dataItem, $index);
           
       }
     $this->calcTotal();
   }
   
   public function addItem(int $idProduto, int $qtda = 1){
       $this->setItem($idProduto, $qtda,1);
   }
   public function deleteItem(int $idProduto){
        $this->setItem($idProduto,0,3);
   }
   public function calcTotal(){
       $total = [
           'weight' => 0,//Peso em kg (Soma total)
           'price'  => 0,//Preco (Soma total)
           'width'  => 0,//Largura em cm (Max)
           'height' => 0,//Altura em cm (Max)
           'length' => 0//Comprimento em cm (Soma total)
       ];
       
       foreach ($this->session->getItens() as $item){
           $total['weight'] += $item['total']['weight'];
           $total['price'] += $item['total']['price'];
           $total['length'] += $item['total']['length'];
           
           if($item['total']['width']>$total['width'])
               $total['width'] = $item['total']['width'];
           if($item['total']['height']>$total['height'])
               $total['height'] = $item['total']['height'];
       }
       
       $total['weight'] = round($total['weight'],1);
       $total['price'] = round($total['price'],2);
       $total['length'] = round($total['length']);
       $total['width'] = round($total['width']);
       $total['height'] = round($total['height']);
           
       $this->set('total', $total);
       
       $this->calcShipping();
   }
   public function calcShipping($types = null){
       $sCepDestino = $this->get('address.zipcode');
       $peso = $this->get('total.weight');
       $priceTotal = $this->get('total.price');
       
       if(empty($sCepDestino) || $peso<=0){
           $this->set('shipping',[]);
           return true;
       }
       if($priceTotal>0) 
            Config::set('nVlValorDeclarado', $priceTotal);
       /*Peso da encomenda, incluindo sua embalagem. O
        peso deve ser informado em quilogramas. Se o
        formato for Envelope, o valor máximo permitido será 1
        kg.*/
       Config::set('nVlPeso', $peso);
       
       $width = $this->get('total.width');//22
       $height = $this->get('total.height');//2
       $length = $this->get('total.length');//33
       
       if($width<22 && $height<3 && $height<33){
           Config::set('nCdFormato', Config::FORMATO_ENVELOPE);
       } else {
           $width += 4;
           $height += 4;
           $length += 4;
           Config::set('nCdFormato', Config::FORMATO_CAIXA);
       }
       
       Config::set('nVlComprimento', $length);//(incluindo embalagem), em centímetros
       Config::set('nVlAltura', $height);//(incluindo embalagem), em centímetros
       Config::set('nVlLargura', $width);//(incluindo embalagem), em centímetros
       
       Config::set('sCepDestino', $sCepDestino);
       
	   if(is_null($types) || empty($types)){
			$types = "1|2|3";//0 - NENHUM / 1 - PAC / 2 - Sedex Hoje / 3 - Sedex  
	   } 
		$servicos = [
		    0,
		    Config::SERVICO_PAC,
		    Config::SERVICO_SEDEX_HOJE,
		    Config::SERVICO_SEDEX
		];
		
		$types = preg_replace('/[^0-9|]/', '', $types);
		$types = explode("|", $types);
	   
	   $r = [];
	   foreach($types as $type){
	       if(isset($servicos[ $type ]))
	           Config::nCdServicoAppend($servicos[ $type ]);
	   }
	   
	   $ECT = new ECT();
	   $r = $ECT->CalcPrecoPrazo();
	   $shipping = [];
	   foreach($r as $result){
	       if($result['erro']===false){
    	       switch ($result['codigo']){
    	           case (Config::SERVICO_PAC):
    	               $shipping[1] = [
    	                   'title' => "PAC - Encomenda econômica",
    	                   'value' => $result['valor'],
    	                   'error' => false,
    	                   'strError' => $result['msgErro'],
    	                   'timeMin' => $result['prazoEntrega'],
    	                   'timeMax' => $result['prazoEntrega']+4
    	               ];
    	               break;
    	           case (Config::SERVICO_SEDEX_HOJE):
    	               $shipping[2] = [
        	               'title' => "SEDEX HOJE",
        	               'value' => $result['valor'],
        	               'error' => false,
        	               'strError' => $result['msgErro'],
        	               'timeMin' => $result['prazoEntrega'],
        	               'timeMax' => $result['prazoEntrega']+4
    	               ];
    	               break;
    	           case (Config::SERVICO_SEDEX):
    	               $shipping[3] = [
        	               'title' => "SEDEX",
        	               'value' => $result['valor'],
        	               'error' => false,
        	               'strError' => $result['msgErro'],
        	               'timeMin' => $result['prazoEntrega'],
        	               'timeMax' => $result['prazoEntrega']+4
    	               ];
    	               break;
    	       }
	       } elseif((int)$result['erro']<0 || $result['erro']=='7') {
	           switch ($result['codigo']){
    	           case (Config::SERVICO_PAC):
    	               $shipping[1] = [
    	                   'title' => "PAC - Encomenda econômica",
    	                   'error' => $result['erro'],
    	                   'strError' => $result['msgErro']
    	               ];
    	               break;
    	           case (Config::SERVICO_SEDEX_HOJE):
    	               $shipping[2] = [
        	               'title' => "SEDEX HOJE",
        	               'value' => $result['valor'],
    	                   'error' => $result['erro'],
    	                   'strError' => $result['msgErro']
    	               ];
    	               break;
    	           case (Config::SERVICO_SEDEX):
    	               $shipping[3] = [
        	               'title' => "SEDEX",
    	                   'error' => $result['erro'],
    	                   'strError' => $result['msgErro']
    	               ];
    	               break;
    	       } 
	       }
	   }
	   $this->set('shipping',$shipping);
	   return true;
   }
}
