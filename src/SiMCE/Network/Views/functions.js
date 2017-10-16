/**
 * 
 * Bloco de funções do módulo.
 * 
 * @returns void
 */

SiMCE.functions = {

	/**
	 * Cria a estrutura da rede de relacionamentos
	 * 
	 * @param int mode
	 * @returns void
	 */
	createNetwork: function( filter ) {
		
	/*          $jit.RGraph.Plot.EdgeTypes.implement({
				'labeled': {
				  'render': function(adj, canvas) {
					this.edgeTypes.line.render.call(this, adj, canvas);
					var data = adj.data;
					//if(data.labeltext) {
					  var ctx = canvas.getCtx();
					  var posFr = adj.nodeFrom.pos.getc(true);
					  var posTo = adj.nodeTo.pos.getc(true);
					  //ctx.fillText(data.labeltext, (posFr.x + posTo.x)/2, (posFr.y + posTo.y)/2);
					  ctx.fillText("TESTE", (posFr.x + posTo.x)/2, (posFr.y + posTo.y)/2);
					//}// if data.labeltext
				  }
				}
			  });*/


		// Carrega a rede via AJAX
		var mode = 1;
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "view/?operacao=" + SiMCE.getOperacao() + "&mode=" + mode + ((filter) ? "&" + filter : ""),
			{},
			function( data, textStatus, jqXHR ) {

				// Verifica se retornou as informações com sucesso
				if ( data.success === true ) {

					// Limpa o conteúdo anterior
					$('#network-embed').empty();

					var rgraph = new $jit.RGraph({  
						injectInto: 'network-embed',  
						background: false,//{  
						//Set Node and Edge styles.  
						Node: {  
							color: '#000000'  
						},  
						Edge: {  
						  color:     '#C17878',  
						  lineWidth: 1.5,
						},  

						onBeforeCompute: function(node){ 

							$('#network-info').hide();
							if (!node.data.hasOwnProperty("type"))
								return;

							// Obtem os nodos filhos
							var nodes = new Array();
							$.each( node.adjacencies, function( key, value ) {
								if ( key != 0 ) {
									nodes.push( key );
								}
							});

							// Carrega os detalhes do Alvo/Interlocutor
							SiMCE.ajaxRequest(
								"POST",
								SiMCE.moduleURL + "details/", 
								"id=" + node.id + "&type=" + node.data.type + "&nodes=" + nodes.join("|"),
								function( data, textStatus, jqXHR ) {
									if ( data.success === true ) {
										$('#network-info').html( data.content ).show();
									}
								}
							);


						},
						//Add the name of the node in the correponding label  
						//and a click handler to move the graph.  
						//This method is called once, on label creation.  
						onCreateLabel: function(domElement, node){  
							domElement.innerHTML = '<span>' + node.name + '</span>';
							domElement.onclick = function(){  
								rgraph.onClick(node.id, {  
									onComplete: function() {  
										//alert("Click onCreateLabel");
									}
								});  
							};  
						},  
						//Change some label dom properties.  
						//This method is called each time a label is plotted.  
						onPlaceLabel: function(domElement, node){  
							var style = domElement.style;  
							style.display = '';  
							style.cursor = 'pointer';  

							if (node._depth <= 1) {  
								style.fontSize = "0.8em";  
								style.color = "#0000";  

							} else if(node._depth == 2){  
								style.fontSize = "0.7em";  
								style.color = "#0000";  

							} else {  
								style.display = 'none';  
							}  

							var left = parseInt(style.left);  
							var w = domElement.offsetWidth;  
							style.left = (left - w / 2) + 'px';  
						}  
					});  
					//load JSON data  
					rgraph.loadJSON(data.content);  
					//trigger small animation  
					rgraph.graph.eachNode(function(n) {  
					  var pos = n.getPos();  
					  pos.setc(-200, -200);  
					});  
					rgraph.compute('end');  
					rgraph.fx.animate({  
					  modes:['polar'],  
					  duration: 2000  
					});  

				}

			}
		);
		
	},
	
	/**
	 * Troca o modo de visualização
	 * 
	 * @param html element self
	 * @returns void
	 */
	changeMode: function( self ) {
		
		// Verifica se foi clicado e modifica o modo
		if ($(self).is(':checked') === true) {
			
			SiMCE.functions.createNetwork( $(self).val() );
			
		}
		
	},
	
	/**
	 * Exporta a imagem e força o download no navegador
	 * 
	 * @returns void
	 */
	exportImage: function( event ) {
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "graph/?operacao=" + SiMCE.getOperacao(),
			{},
			function( data, textStatus, jqXHR ) {
				if (data.success === true) {
					// Força o download da imagem
					var link = document.createElement("a");
					link.download = "rede_relacionamento.png";
					link.href = "data:image/png;base64," + data.content;
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
					delete link;
				}
			}
		);
				
	},
	
	/**
	 * Obtem a lista de alvos do usuário
	 * 
	 * @returns void
	 */
	getTargetList: function() {
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "targets/",
			{ operacao: SiMCE.getOperacao() },
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					$.each( data.content, function( index, obj ) {
						$('*[name="filter[alvos_id][]"]').append('<option value="'+obj.id+'">'+obj.nome+'</option>');
					});
				
				}
				
			}
		);
		
	},
	
	/**
	 * Obtem a lista de contatos do usuário
	 * 
	 * @returns void
	 */
	getContactList: function() {
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "contacts/",
			{ operacao: SiMCE.getOperacao() },
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					$.each( data.content, function( index, obj ) {
						$('*[name="filter[contatos_id][]"]').append('<option value="'+obj.id+'">'+obj.nome+'</option>');
					});
				
				}
				
			}
		);
		
	},
	
	/**
	 * Aplica o filtro na rede de relacionamentos
	 * 
	 * @returns void
	 */
	applyFilter: function() {
		
		// Obtem a configuração do filtro
		var filter = $("#form-grid-filter").serialize();
		SiMCE.functions.createNetwork( filter );
		$('.grid-filter-clean').removeClass('disabled');
		
	},
	
	/**
	 * Limpa o filtro da rede
	 * 
	 * @returns void
	 */
	cleanFilter: function() {
		
		SiMCE.functions.createNetwork();
		$('.grid-filter-clean').addClass('disabled');
		
	},
	
	filterRecords: function( type, target, contact ) {
		
		if (type == 0) {
			$('*[name="data[alvo]"]').val(target);
			$('*[name="data[contato]"]').val(contact);
		} else {
			$('*[name="data[alvo]"]').val(contact);
			$('*[name="data[contato]"]').val(target);
		}

		// Carrega a tela de interceptações
		$('a[href="#interceptacoes"]').tab('show');
		
	}
	
};