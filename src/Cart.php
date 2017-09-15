<?php

namespace SimpleCheckout;

class Cart
{
   const SHIPPING_SEDEX = 40010;//SEDEX Varejo
   const SHIPPING_SEDEX_A_COBRAR = 40045;//SEDEX a Cobrar Varejo
   const SHIPPING_SEDEX_10 = 40215;//SEDEX 10 Varejo
   const SHIPPING_SEDEX_HOJE = 40290;//SEDEX Hoje Varejo
   const SHIPPING_PAC = 41106;//PAC Varejo
    
   private $session;/*
   private $itens;
   private $data;*/
   
   public function __construct(){
       $this->session = Session::getSession();
       
       if(empty( $this->session->get() ) ){
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
   /**
    * 
    * @param int idProduct Id do produto
    * @param int $amount Quantidade
    * @param string $action 0 - Alterar item (p.e.: q = newQ ) / 1 - Incrementar(qtda) item (p.e.: q += newQ)/ 2 - Decrementar(qtda) item(p.e.: q -= newQ) / 3 - Delete Item
    */
   public function setItem(int $id_product, int $amount = 1,int $action = 0){
       $dataProduct = Product::select('id','title','price','weight','digital')->find($id_product);
       $amountItem = 0;
       $index = null;
       foreach ($this->session->getItens() as $i=>$item){
           if($item['id_product']==$id_product){
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
                return $this->session->deleteItem( $index );
           return false;
       } else {//Salvar Item
           $dataItem = [
               'product' => $dataProduct,
               'amount' => $amountItem,
               'total' => [
                   'weight' => ($dataProduct['weight']/1000 * $amountItem),//Peso em kg (Soma total)
                   'price'  => ($dataProduct['price'] * $amountItem),//Preco (Soma total)
                   'width'  => ceil($dataProduct['width']/100),//Largura em cm (Max)
                   'height' => ceil($dataProduct['height']/100),//Altura em cm (Max)
                   'length' => ceil($dataProduct['length']/100)*$amountItem//Comprimento em cm (Soma total)
               ]
           ];
           
           return $this->session->putItem($dataItem, $index);
       }
       
       $this->refreshData();
   }
   
   public function addItem(int $idProduto, int $qtda = 1){
       $this->setItem($idProduto, $qtda,1);
   }
   public function delItem(int $idProduto){
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
       
       $total['weight'] = round($item['total']['weight'],1);
       $total['price'] = round($item['total']['price'],2);
       $total['length'] = round($item['total']['length']);
       $total['width'] = round($item['total']['width']);
       $total['height'] = round($item['total']['height']);
           
       $this->set('total', $total);
       
       $this->calcShipping();
   }
   public function calcShipping($types = null){
       /*
        *  $this->dataTotal = [
            'weight' => DB::table('cart_items')
                ->where('id_session', '=', $this->IdSession )
                ->sum('weight_total'),
            'price' => DB::table('cart_items')
                ->where('id_session', '=', $this->IdSession )
                ->sum('price_total')
        ];
        */
       //DEFAULT------------------
       \Correios\Config::set('nVlDiametro',0);
       Config::set('sCdMaoPropria','N');
       Config::set('nVlValorDeclarado',$this->get('total.price');
       Config::set('sCdAvisoRecebimento','N');
       Config::set('sCepOrigem', '30140070');
       //DEFAULT------------------
       
       /*Peso da encomenda, incluindo sua embalagem. O
        peso deve ser informado em quilogramas. Se o
        formato for Envelope, o valor máximo permitido será 1
        kg.*/
       Config::set('nVlPeso', $this->get('total.weight'));
       Config::set('nCdFormato', Config::FORMATO_ENVELOPE);
       Config::set('nVlComprimento', $this->get('total.width'));//(incluindo embalagem), em centímetros
       Config::set('nVlAltura', $this->get('total.length'));//(incluindo embalagem), em centímetros
       Config::set('nVlLargura', $this->get('total.height'));//(incluindo embalagem), em centímetros
       Config::set('sCepDestino', self::getHead('zipcode'));
       
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
	   $r =$ECT->CalcPrecoPrazo();
	   $return = [];
	   foreach($r as $result){
	       if($result['erro']===false){
    	       switch ($result['codigo']){
    	           case (Config::SERVICO_PAC):
    	               $return[1] = [
    	                   'titulo' => "PAC - Encomenda econômica",
    	                   'valor' => $result['valor'],
    	                   'prazoMin' => $result['prazoEntrega'],
    	                   'prazoMax' => $result['prazoEntrega']+4
    	               ];
    	               break;
    	           case (Config::SERVICO_SEDEX_HOJE):
    	               $return[2] = [
    	                   'titulo' => "SEDEX HOJE",
        	               'valor' => $result['valor'],
        	               'prazoMin' => $result['prazoEntrega'],
        	               'prazoMax' => $result['prazoEntrega']+4
    	               ];
    	               break;
    	           case (Config::SERVICO_SEDEX):
    	               $return[3] = [
    	                   'titulo' => "SEDEX",
        	               'valor' => $result['valor'],
        	               'prazoMin' => $result['prazoEntrega'],
        	               'prazoMax' => $result['prazoEntrega']+4
    	               ];
    	               break;
    	       }
	       }
	   }
	   
	   return $return;
   }
}
