SiMCE.functions = {
	
	/**
	 * Armazena as informações do registros selecionado
	 * 
	 * @type Object
	 */
	data: null,
	
	/**
	 * Exibe o formulário
	 * 
	 * @returns void
	 */
	showForm: function( id, mode ) {
	
		var mode = mode || 0;
		var url = SiMCE.moduleURL + "form/";
		if (id)
			url += "?id=" + id + "&mode=" + mode;
	
		// Exibe
		$("#app-modal").modal({
			remote:   url,
			backdrop: "static",
			show:     true,
			keyboard: false
		//}).on('show.bs.modal', function() {
		//	$(this).html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><br><center><img src="assets/img/loader.gif">Carregando...</center><br></div></div></div>');
		//}).on('hide.bs.modal', function() {
		//	$(this).empty();
		}).on('hidden.bs.modal', function () {
			$(this).removeData('bs.modal');
		});
		
	},
	
	/**
	 * Exibe o formulário para atualizar
	 * informações
	 * 
	 * @returns void
	 */
	editForm: function() {
	
		// Obtem os dados selecionados do grid para exibir o formulário
		SiMCE.functions.showForm( SiMCE.functions.data.id );
		
	},
			
	/**
	 * Remove o registro selecionado
	 * 
	 * @returns void
	 */
	removeForm: function() {
	
		// Obtem os dados selecionados do grid para exibir o formulário
		bootbox.confirm("Tem certeza que deseja remover o alvo " + SiMCE.functions.data.nome + "?", function(result) {
			
			// Envia os dados para remoção
			if (result === true) {
				SiMCE.ajaxRequest(
					"POST",
					SiMCE.moduleURL + "remove/", 
					{ id: SiMCE.functions.data.id },
					function( data, textStatus, jqXHR ) {
						
						if ( data.success === true ) {
							// Mensagem para o usuário
							SiMCE.successMessage("Alvo removido com sucesso!");
						}
						
						// Recarrega o grid
						$('#datagrid').dataTable({ bRetrieve: true }).fnDraw();
						
					}
				);
			}

		});
		
		
	},

	/**
	 * Faz a submissão por ajax dos dados
	 * do formulário
	 * 
	 * @returns void
	 */
	submit: function() {

		// Reset do formulário
		$('.has-error').removeClass("has-error");

		var arrAloc = '';
		var nodes = $('#datagrid-alocacoes').dataTable().fnGetNodes();
		$.each( nodes, function( idx, node ) {
			var data = $('#datagrid-alocacoes').dataTable().fnGetData( node );
			for( var x in data ) {
				arrAloc += "&alocacoes[" + idx + "][" + x + "]=" + encodeURIComponent(data[x]);
			}
		});
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "save/",
			$("#form-alvos").serialize() + "&operacao=" + SiMCE.getOperacao() + arrAloc,
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Esconde a janela modal
					$("#app-modal").modal("hide");
				
					// Mensagem para o usuário
					SiMCE.successMessage("Alvo cadastrado/atualizado com sucesso!");
				
					// Recarrega o grid
					$('#datagrid').dataTable({ bRetrieve: true }).fnDraw();
					
				} else { // Em caso de erro
					$.each( data.fields, function( index, key ) {
						$('*[name="data[' + key + ']"]').parent().addClass("has-error");
					});
				}
				
			}
		);

	},
	
	/**
	 * Carrega o formulário de novas alocações
	 * 
	 * @param int index
	 * @returns void
	 */
	loadAlocationForm: function( index ) {
		
		if (typeof index === "undefined") { // Se for uma alocação nova
			
			if ($("#recurso-combo").val() != 0) {

				SiMCE.ajaxRequest(
					"POST",
					SiMCE.moduleURL + "loadAlocation/",
					"operacao=" + SiMCE.getOperacao(),
					function( data, textStatus, jqXHR ) {
						if (data.success === true) {
							$('#nova-alocacao').html( data.content ).show();
							$("#recurso-combo").prop("disabled", true);
						}
					}
				);

			} else {
				$('#nova-alocacao').empty().hide();
			}
		} else {  // Se for edição
			
			var obj = $('#datagrid-alocacoes').dataTable().fnGetData()[index];
			SiMCE.ajaxRequest(
				"POST",
				SiMCE.moduleURL + "loadAlocation/",
				"operacao=" + SiMCE.getOperacao() + "&index=" + index + "&id=" + obj.id,
				function( data, textStatus, jqXHR ) {
					if (data.success === true) {
						$('#nova-alocacao').html( data.content ).show();
						$("#recurso-combo").prop("disabled", true);
						// Adiciona conteúdo caso não tenha sido salvo ainda
						if (obj.id == 0) {
							$('*[name="alocacao[inicio]"]').val( obj.inicio );
							$('*[name="alocacao[fim]"]').val( obj.fim );
							$('*[name="alocacao[identificacao]"]').val( obj.identificacao );
							$('*[name="alocacao[oficio]"]').val( obj.oficio );
							$('*[name="alocacao[desvio_para]"]').val( obj.desvio_para );
							$('*[name="alocacao[desvio_via]"]').val( obj.desvio_via );
						}
						
					}
				}
			);
			
		}
		
	},
	
	/**
	 * Adiciona a alocação do formulário no grid
	 * 
	 * @returns void
	 */
	addAlocation: function() {
		
		var inicio = $('*[name="alocacao[inicio]"]').val();
		var fim    = $('*[name="alocacao[fim]"]').val();
		var ident  = $('*[name="alocacao[identificacao]"]').val();
		var oficio = $('*[name="alocacao[oficio]"]').val() || '-';
		var d_para = $('*[name="alocacao[desvio_para]"]').val();
		var d_via  = $('*[name="alocacao[desvio_via]"]').val();
		
		if (ident == '') {
			$('*[name="alocacao[identificacao]"]').parent().addClass("has-error");
		} else {
		
			SiMCE.ajaxRequest(
				"POST",
				SiMCE.moduleURL + "checkAlocation/",
				"operacao=" + SiMCE.getOperacao() + "&recurso=" + $('select[id=recurso-combo]').val() + "&inicio=" + inicio + "&fim=" + fim + "&ident=" + ident + "&d_para=" + d_para + "&d_via=" + d_via,
				function( data, textStatus, jqXHR ) {
					if (data.success === true) {

						$('#datagrid-alocacoes').dataTable().fnAddData({
							id:            0,
							recurso:       $('select[id=recurso-combo] option:selected').text(),
							inicio:        inicio,
							fim:           fim,
							identificacao: ident,
							oficio:        oficio,
							desvio_para:   d_para,
							desvio_via:    d_via,
							acao:          ''
						});

						$('#nova-alocacao').empty().hide();
						$('#recurso-combo').val(0);
						$("#recurso-combo").prop("disabled", false);

					}
				}
			);

		}
		
	},

	/**
	 * Cancela a edição da alocação
	 * 
	 * @returns void
	 */
	cancelAlocation: function() {
			$('#nova-alocacao').empty().hide();
			$('#recurso-combo').val(0);
			$("#recurso-combo").prop("disabled", false);
	},
			
	/**
	 * Atualiza a alocação
	 * 
	 * @param int index
	 * @returns void
	 */
	updateAlocation: function( index ) {

		var curr = $('#datagrid-alocacoes').dataTable().fnGetData()[index];
		var nObj = {
			id:            curr["id"] || 0,
			recurso:       curr["recurso"],
			inicio:        $('*[name="alocacao[inicio]"]').val(),
			fim:           $('*[name="alocacao[fim]"]').val(),
			identificacao: $('*[name="alocacao[identificacao]"]').val(),
			oficio:        $('*[name="alocacao[oficio]"]').val() || '-',
			desvio_para:   $('*[name="alocacao[desvio_para]"]').val(),
			desvio_via:    $('*[name="alocacao[desvio_via]"]').val(),
			acao:          ''
		};
		
		if (nObj.identificacao == '') {
			$('*[name="alocacao[identificacao]"]').parent().addClass("has-error");
		} else {
		
			SiMCE.ajaxRequest(
				"POST",
				SiMCE.moduleURL + "checkAlocation/",
				"operacao=" + SiMCE.getOperacao() + "&recurso=" + $('select[id=recurso-combo]').val() + "&inicio=" + nObj.inicio + "&fim=" + nObj.fim + "&id=" + nObj.id  + "&d_para=" + nObj.desvio_para + "&d_via=" + nObj.desvio_via,
				function( data, textStatus, jqXHR ) {
					if (data.success === true) {

						$('#datagrid-alocacoes').dataTable().fnUpdate( nObj, index );
						$('#nova-alocacao').empty().hide();
						$('#recurso-combo').val(0).prop("disabled", false);

					}
				}
			);		
		

		}

	},

	/**
	 * Remove a alocação do grid
	 * 
	 * @param int index
	 * @returns void
	 */
	removeAlocation: function( index ) {

		// Obtem os dados selecionados do grid para exibir o formulário
		bootbox.confirm("Tem certeza que deseja remover a alocação?", function(result) {
			
			// Remove a alocação do grid
			if (result === true) {
				$('#datagrid-alocacoes').dataTable().fnDeleteRow( index );
			}

		});

	},

	/**
	 * Carrega o formulário de permissões
	 * 
	 * @returns void
	 */
	loadPermissionForm: function() {

		var id   = $('select[id=usuario-combo]').val();
		var nome = $('select[id=usuario-combo] option:selected').text();
		
		/**
		 * Verifica se já existe o usuário no form
		 */
		var found = 0;
		$('.perm-form-id').each( function( index, el ) {
			if (el.value == id) {
				found = 1;
			}
		});
		
		if (!found) {
			SiMCE.ajaxRequest(
				"POST",
				SiMCE.moduleURL + "loadPermission/",
				"id=" + id + "&nome=" + nome,
				function( data, textStatus, jqXHR ) {
					if (data.success === true) {
						$('select[id=usuario-combo]').val(0);
						$('#permissoes').append( data.content );
					}
				}
			);
		} else {
			$('select[id=usuario-combo]').val(0);
		}

	},
	
	/**
	 * Remove a permissão selecionada
	 * 
	 * @param int id
	 * @returns void
	 */
	removePermission: function( id ) {
		
		bootbox.confirm("Tem certeza que deseja remover esta permissão?", function(result) {
			
			// Remove a alocação do grid
			if (result === true) {
				$('#perm-form-' + id).remove();
			}

		});
		
	}
	
};
