<!doctype html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Carrinho</title>
        <link rel="manifest" href="manifest.json">
    	<link rel="shortcut icon" href="favicon.ico">
        <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="/fontawesome/css/font-awesome.css" rel="stylesheet" type="text/css">
		<link href="/style.css" rel="stylesheet" type="text/css">
 		<script type="text/javascript" src="/js/jquery-2.2.2.min.js"></script>
		<script type="text/javascript" src="/js/kf.min.js"></script>
		<script type="text/javascript" src="/js/app.js"></script>
    </head>
    <body>
    	<nav id="nav_main" class="navbar navbar-default navbar-fixed-top">
	<div class="container-fluid">
    	<div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#btn-navbar-collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">Exemplo SimpleCheckout</a>
        </div>
        <div class="collapse navbar-collapse" id="btn-navbar-collapse">
      		<ul class="nav navbar-nav">
        		<li><a href="/" onclick="cart.addItem(1);return false;"><i class="fa fa-plus" aria-hidden="true"></i> Inserir Produto 1(Calça)</a></li>
				<li><a href="/" onclick="cart.addItem(2);return false;"><i class="fa fa-plus" aria-hidden="true"></i> Inserir Produto 2(T-Shirt)</a></li>
        	</ul>
        </div>
	</div>
</nav>
	<div class="container">
	<h3>Itens do carrinho</h3>
	<table class="table">
		<thead>
			<tr>
				<th style="width: 1px;">#</th>
				<th>Produto</th>
				<th style="width: 1px;">Preço(unitário)</th>
				<th style="width: 70px;">Qtda</th>
				<th style="width: 1px;">Valor</th>
				<th style="width: 1px;">&nbsp;</th>
			</tr>
		</thead>
		<tbody id="itensCart">
			<tr>
				<td colspan="5"><strong>Sem itens no carrinho</strong></td>
			</tr>
		</tbody>
	</table>
	<hr />
	<div class="row">
		<script>
		var cart = new Cart([]);
		$(document).ready(function(){
			cart.refreshData(<?php echo json_encode($dataCart);?>);
			$('#field_zipcode').change(function(){
				cart.queryCEP( this.value );
			});
			$('#btnQueryCEP').click(function(){
				cart.queryCEP( $('#field_zipcode').val() );
			});
			kf.maskfield('#field_zipcode','99.999-999',true,true);
		});
		</script>
		
		<div class="col-md-3 col-sd-12">
			<h5><strong>1 - CEP</strong></h5>
			<div class="input-group">
      			<input class="form-control" type="text" id="field_zipcode" value="" />
        		<span class="input-group-btn">
                    <button class="btn btn-primary" id="btnQueryCEP" type="button">Buscar</button>
                </span>
    		</div>
    		<div id="dvAddress"></div> 
    	</div>
    	<div class="col-md-6 col-sd-12">
    		<h5><strong>2 - Forma de envio</strong></h5>
    		<div class="row" id="dvShipping"></div>
    	</div>
    	<div class="col-md-3 col-sd-12">
    		<h5><strong>3 - Pagamento</strong></h5>
    		<div id="dvTotais"></div>
    		<a href="/" class="btn btn-success btn-block disabled" id="btnPag">Pagar</a>
    	</div>
	</div>
	</div>
    </body>
</html>
