SiMCE.functions = {

	/**
	 * Gera o PDF
	 * 
	 * @returns void
	 */
	genPDFReport: function() {

		var content = $('*[name="data[conteudo]"]');
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "gen_report/",
			{ data: content.val() },
			function( data, textStatus, jqXHR ) {

				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {

					window.open( data.file, "SiMCE - Recursos de Interceptação" );

				}

			}
		);
	
	}

};
