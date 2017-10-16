SiMCE.functions = {

	/**
	 * Faz a submissão por ajax dos dados
	 * do formulário
	 * 
	 * @returns void
	 */
	submit: function() {

		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "save/",
			$("#form-config").serialize() + '&data[relatorio_cabecalho]=' + encodeURIComponent(tinyMCE.get('data[relatorio_cabecalho]').getContent()) + '&data[relatorio_rodape]=' + encodeURIComponent(tinyMCE.get('data[relatorio_rodape]').getContent()),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Mensagem para o usuário
					SiMCE.successMessage("Configurações salvas com sucesso!");
					
				}
				
			}
		);

	}
	
};
