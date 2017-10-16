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
	showForm: function( id ) {
	
		var url = SiMCE.moduleURL + "form/";
		if (id)
			url += "?id=" + id;
	
		// Exibe
		$("#app-modal").modal({
			remote:   url,
			backdrop: "static",
			show:     true,
			keyboard: false
		}).on('show.bs.modal', function() {
			$(this).html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><br><center><img src="assets/img/loader.gif">Carregando...</center><br></div></div></div>');
		}).on('hide.bs.modal', function() {
			$(this).empty();
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
		bootbox.confirm("Tem certeza que deseja remover o usuário " + SiMCE.functions.data.nome + "?", function(result) {
			
			// Envia os dados para remoção
			if (result === true) {
				SiMCE.ajaxRequest(
					"POST",
					SiMCE.moduleURL + "remove/", 
					{ id: SiMCE.functions.data.id },
					function( data, textStatus, jqXHR ) {
						
						if ( data.success === true ) {
							// Mensagem para o usuário
							SiMCE.successMessage("Usuário removido com sucesso!");
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

		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "save/",
			$("#form-usuarios").serialize(),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Esconde a janela modal
					$("#app-modal").modal("hide");
				
					// Mensagem para o usuário
					SiMCE.successMessage("Usuário cadastrado/atualizado com sucesso!");
				
					// Recarrega o grid
					$('#datagrid').dataTable({ bRetrieve: true }).fnDraw();
					
				} else { // Em caso de erro
					$.each( data.fields, function( index, key ) {
						$('*[name="data[' + key + ']"]').parent().addClass("has-error");
					});
				}
				
			}
		);

	}
	
};