/**
 * 
 * Bloco de funções do módulo.
 * 
 * @returns void
 */

SiMCE.functions = {

	/**
	 * Contem o objeto timeline
	 */
	timeline: null,

	/**
	 * Carrega a timeline
	 * 
	 * @returns void
	 */
	loadTimeline: function() {
	
		// Remove o conteúdo anterior
		$('#timeline-embed').empty();
	
		// Inicializa a timeline
		var timeline_config = {
			type:       'timeline',
			width:      '100%',
			height:     '400',
			//source:     config,
			source:     SiMCE.moduleURL + "view/?operacao=" + SiMCE.getOperacao(),
			embed:      true,
			embed_id:   'timeline-embed',
			lang:       'pt-br'
		};

		SiMCE.functions.timeline = new VMM.Timeline('timeline-embed', '100%', '400');
		SiMCE.functions.timeline.init(timeline_config);
		
	},

	/**
	 * Exibe o formulário
	 * 
	 * @returns void
	 */
	showForm: function( id ) {
	
		var url = SiMCE.moduleURL + "form/";
	
		// Exibe
		$("#app-modal").modal({
			remote:   url,
			backdrop: "static",
			show:     true,
			keyboard: false
		}).on('hidden.bs.modal', function () {
			$(this).removeData('bs.modal');
		});
		
	},
	
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
			$("#form-timeline").serialize() + "&operacao=" + SiMCE.getOperacao() + '&data[descricao]=' + encodeURIComponent(tinyMCE.get('data[descricao]').getContent()),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Esconde a janela modal
					$("#app-modal").modal("hide");
				
					// Mensagem para o usuário
					SiMCE.successMessage("Elemento adicionado com sucesso!");
				
					// Recarrega a timeline
					SiMCE.functions.loadTimeline();
				
				} else { // Em caso de erro
					$.each( data.fields, function( index, key ) {
						$('*[name="data[' + key + ']"]').parent().addClass("has-error");
					});
				}
				
			}
		);

	},
	
	/**
	 * Adicionar o mini player no seletor informado
	 * 
	 * @param string selector
	 * @returns void
	 */
	addMiniAudioPlayer: function( placeholder, tagid, url, e ) {
		
		if (e)
			e.stopPropagation();

		// Faz a requisição
		SiMCE.ajaxRequest(
			"GET",
			url,
			null,
			function( data, textStatus, jqXHR ) {
				if (data.success === true) {
					
					if ($("#" + tagid).length)
						return;
					
					// Exibe o player
					if ( $.browser.msie ) {
						$('#' + placeholder).html("<embed id='" + tagid + "' type='audio/x-wav' src='" + data.content + "' autoplay='true' autostart='true' width='200' height='50'>");
					} else {
						$('#' + placeholder).html("<audio id='" + tagid + "'src='" + data.content + "'></audio>");
						$('#' + tagid).mediaelementplayer({
							audioWidth: 150,
							features: ['playpause', 'progress']
						});
					}
					
				}
			}
		);
		
		
	}
	
};