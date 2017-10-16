SiMCE.login = {
	
	submit: function( form ) {
		
		// Exibe o loader
		$("#loader").show();
		
		// Submete os dados para verificação
		SiMCE.ajaxRequest(
			"POST",
			"/simce/do_login/",
			$("#form-login").serialize(),
			function( data, textStatus, jqXHR ) {
				
				// Exibe o loader
				$("#loader").hide();
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Faz o redirect
					window.location = "/simce/";
				
				}
				
			}
		);
		
		return false;
		
	}
	
}