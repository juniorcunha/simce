SiMCE.functions = {
	
	/**
	 * Carrega os recursos da unidade selecionada
	 * 
	 * @returns void
	 */
	showForm: function() {
	
		var id = $('select[name="data[unidade]"]').val();
		if (id > 0) {
			
			// Carrega as informações desta unidade
			SiMCE.ajaxRequest(
				"POST",
				SiMCE.moduleURL + "form/", 
				{ id: id },
				function( data, textStatus, jqXHR ) {
					if (data.success === true) {
						
						// Atualiza o conteúdo do formulário
						$(".form-resources").html( data.content );
						$(".btn-add").removeClass("disabled");
						
						// Cria o Dual List Box
						var cfg = {
							showfilterinputs:        false,
							preserveselectiononmove: false,
							moveonselect:            false,
							nonselectedlistlabel:    'Disponíveis',
							selectedlistlabel:       'Selecionados'
						};
						$('select[name="data[audio][]"]').bootstrapDualListbox(cfg);
						$('select[name="data[gsm][]"]').bootstrapDualListbox(cfg);
						$('select[name="data[dados][]"]').bootstrapDualListbox(cfg);
					}
				}
			);
			
		} else {
			$(".form-resources").html("");
			$(".btn-add").addClass("disabled");
		}
		
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
			$("#form-rec-intr").serialize(),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Mensagem para o usuário
					SiMCE.successMessage("Recursos definidos com sucesso!");
				
				} else { // Em caso de erro
					$.each( data.fields, function( index, key ) {
						$('*[name="data[' + key + ']"]').parent().addClass("has-error");
					});
				}
				
			}
		);

	}
	
};
