
class Cart {
	constructor(data){
		this.dataCache = {};
		this.refreshData(data);
	}
	addItem(idProduct, qtda){
		var url = '/api/cart/addItem/'+idProduct;
		
		if(typeof qtda!='undefined')
			url += '/'+qtda;
		
		this.sendRequest(url, null);
	}
	setItem(idProduct, qtda,action){
		var url = '/api/cart/setItem/'+idProduct+'/'+qtda;
		
		if(typeof action!='undefined')
			url += '/'+action;
		
		this.sendRequest(url, null);
	}
	deleteItem(idProduct){
		var url = '/api/cart/deleteItem/'+idProduct;
		
		this.sendRequest(url, null);
	}
	setTypeShipping(typeShipping){
		var url = '/api/cart/setTypeShipping/'+typeShipping;
		
		this.sendRequest(url, null);
	}
	setAddress(dataAddress){
		var url = '/api/cart/setAddress';
		
		this.sendRequest(url, dataAddress, 'POST');
	}
	queryCEP(cepValue){
		cepValue = kf.unmask( cepValue );
		var cart = this;
		kf.queryCEP(cepValue,
			function(data){
				cart.setAddress({
					zipcode: data.cep,
					street: data.logradouro,
					/*number: data.logradouro,
					compl: data.logradouro,*/
					district: data.bairro,
					city: data.cidade,
					state: data.estado
				});
			},
			function(xhr, ajaxOptions, thrownError){
				cart.showError("Erro de comunicação", thrownError+'<br />verifique sua conexão!');
			}
		);
	}
	sendRequest(url,dataSend,method){
		if(typeof method=='undefined')
			var method = 'GET';
		var cart = this;
		kf.ajaxJSON(url,dataSend,{
			method: method,
			fncSuccess: function(response){
				cart.refreshData(response.data);
			},
			fncError: function(response){
				cart.showError("Erro", response.strError);
			},
			fncFailed:	function(xhr, ajaxOptions, thrownError){
				cart.showError("Erro de comunicação", thrownError+'<br />verifique sua conexão!');
			}
		});
	}
	refreshData(data){
		if(this.dataCache!=data){
			this.dataCache	= this.data;		
			this.data		= data;
		}
		//--------------------------------
		if(typeof this.data.data=='undefined')
			return false;
		
		var itensCartHTML = '';
		if(typeof this.data.itens=='object' && kf.sizeObj(this.data.itens)>0){
			$.each(this.data.itens, function(i, item){
				itensCartHTML += "<tr>";
				itensCartHTML += '<td style="width: 1px;text-align:right;">'+item.product.id+'</td>';
				itensCartHTML += '<td>'+item.product.title+'</td>';
				itensCartHTML += '<td style="width: 1px;text-align:right;"><nobr>'+kf.formatMoney(0.001+parseFloat(item.product.price),'R$')+'</nobr></td>';
				itensCartHTML += '<td style="width: 1px;text-align:right;">';
				itensCartHTML += '<input class="form-control" value="'+item.amount+'" onchange="cart.setItem('+item.product.id+', this.value,0);" />';
				itensCartHTML += '</td>';
				itensCartHTML += '<td style="width: 1px;text-align:right;"><nobr>'+kf.formatMoney(0.001+parseFloat(item.total.price),'R$')+'</nobr></td>';
				itensCartHTML += '<td><button class="btn btn-danger" onclick="cart.deleteItem('+item.product.id+');"><i class="fa fa-times"></i></button></td>';
				itensCartHTML += "</tr>";
			});
			itensCartHTML += "<tr>";
			itensCartHTML += '<td colspan="4" style="text-align:right;font-size: 1.2em;" class="text-primary"><strong>Subtotal:</strong></td>';
			itensCartHTML += '<td colspan="2" style="text-align:left;font-size: 1.2em;" class="text-primary"><nobr>'+kf.formatMoney(0.001+parseFloat(this.data.data.total.price),'R$')+'<nobr></td>';
			itensCartHTML += "</tr>";
		} else {
			itensCartHTML += '<tr>';
			itensCartHTML += '<td colspan="5" class="text-primary"><strong>Sem itens no carrinho</strong></td>';
			itensCartHTML += '</tr>';
		}
		$('#itensCart').html(itensCartHTML);
		//----------------------------------
		var dvAddressHTML = '';
		if(typeof this.data.data=='object' && this.data.data!=null && typeof this.data.data.address=='object' && this.data.data.address.street!=''){
			$('#field_zipcode').val( kf.mask(this.data.data.address.zipcode,'99.999-999'));
			dvAddressHTML += '<div>'+this.data.data.address.street+'</div>';
			dvAddressHTML += '<div>'+this.data.data.address.district+'';
			dvAddressHTML += ', '+this.data.data.address.city+'';
			dvAddressHTML += ' - '+this.data.data.address.state+'</div>';
		}
		$('#dvAddress').html(dvAddressHTML);
		//-----------------------------------------------
		var dvShippingHTML = '';
		if(typeof this.data.data=='object' && this.data.data!=null && typeof this.data.data.shipping=='object' && kf.sizeObj(this.data.data.shipping)>0){
			var type_shipping = this.data.data.type_shipping;
			$.each(this.data.data.shipping, function(idShipping,shipping){
				dvShippingHTML += '<div class="col-md-6 col-sd-6">';
				dvShippingHTML += '<table style="width: 100%;">';
				dvShippingHTML += '<tr>';
				if(shipping.error==false){
					dvShippingHTML += '<td>';
					dvShippingHTML += '<input type="radio" name="type_shipping" id="type_shipping_'+idShipping+'" value="'+idShipping+'"';
					if(type_shipping==idShipping)
						dvShippingHTML += ' checked';
					dvShippingHTML += ' onclick="cart.setTypeShipping('+idShipping+');" />';
					dvShippingHTML += '</td>';
				} else {
					dvShippingHTML += '<td>&nbsp;</td>';
				}
				dvShippingHTML += '<td style="padding:15px;">';
				dvShippingHTML += '<label for="type_shipping_'+idShipping+'" style="font-weight:normal;">';
				dvShippingHTML += '<strong>'+shipping.title+'</strong><br />';
				if(shipping.error==false){
					dvShippingHTML += '<strong>Prazo:</strong> '+shipping.timeMin+' a '+shipping.timeMax+' dias para entrega<br />';
					dvShippingHTML += '<strong>Valor:</strong> '+kf.formatMoney(0.001+parseFloat(shipping.value),'R$')+'';
				} else {
					dvShippingHTML += '<span class="text-danger">'+shipping.strError+'</span>';
				}
				dvShippingHTML += '</label></td></tr>';
				dvShippingHTML += '</table></div>';
				
			});
			
		}
		$('#dvShipping').html(dvShippingHTML);
		if(typeof this.data.data=='object' && this.data.data!=null && typeof this.data.data.amount=='object'){
			var dvTotais = '<table class="table">';
			dvTotais += '<tr>';
			dvTotais += '<th style="text-align: right;">Frete: </th>';
			dvTotais += '<td>'+kf.formatMoney(0.001+parseFloat(this.data.data.amount.shipping),'R$')+'</td>';
			dvTotais += '</tr>';
			
			dvTotais += '<tr>';
			dvTotais += '<th style="text-align: right;">Desconto: </th>';
			dvTotais += '<td>'+kf.formatMoney(0.001+parseFloat(this.data.data.amount.descont),'R$')+'</td>';
			dvTotais += '</tr>';

			dvTotais += '<tr>';
			dvTotais += '<th class="text-success" style="text-align: right;font-size: 1.4em;">Total: </th>';
			dvTotais += '<td class="text-success" style="font-size: 1.4em;">'+kf.formatMoney(0.001+parseFloat(this.data.data.amount.total),'R$')+'</td>';
			dvTotais += '</tr>';
			dvTotais += '</table>';
		}
	   $('#dvTotais').html(dvTotais);
	   if(typeof this.data.data=='object' && this.data.data!=null && this.data.data.type_shipping!=null){
		   $('#btnPag').removeClass('disabled');
	   } else {
		   $('#btnPag').addClass('disabled');
	   }
	   
	}
	showError(title, msg){
		console.error(title,msg);
	}
}