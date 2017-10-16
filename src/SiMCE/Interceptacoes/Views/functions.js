/**
 * 
 * Bloco de funções do módulo.
 * 
 * @returns void
 */

SiMCE.functions = {
	
	/**
	 * Armazena o registro selecionado
	 * 
	 * @type Object
	 */
	data: null,

	/**
	 * Armazena o player online
	 * 
	 * @type Object
	 */
	player: null,
	

	/**
	 * Carrega o painel de interceptações online
	 * 
	 * @returns void
	 */
	loadOnlinePanel: function() {
		
		// Verifica se existe ainda o painel
		if ($('#online-panel').length == 0)
			return;
		
		// Faz a requisição
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "online/",
			{ operacao: SiMCE.getOperacao(), mode: $('input[name=online_mode]:checked').val() },
			function( data, textStatus, jqXHR ) {
				if (data.success === true) {
					// Atualiza o conteúdo
					$('#online-panel').html( data.content );
					
					// Agenda a execução para daqui a 1 segundo
					setTimeout( SiMCE.functions.loadOnlinePanel, 1000 );
					
				}
			}
		);
		
	},
	
	/**
	 * Exibe os detalhes da interceptação
	 * 
	 * @returns void
	 */
	showDetails: function( id ) {
	
		var url = SiMCE.moduleURL + "details/";
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
			// Recarrega o grid
			$('#datagrid').dataTable({ bRetrieve: true }).fnDraw();
		});
		
	},
	
	/**
	 * Adicionar o player no seletor informado
	 * 
	 * @param string selector
	 * @returns void
	 */
	addAudioPlayer: function( placeholder, tagid, url, e ) {
		
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
						$('#' + placeholder).html("<embed id='" + tagid + "' type='audio/wave' src='" + data.content + "' autoplay='false' autostart='true' width='300' height='50'>");
					} else {
						$('#' + placeholder).html("<audio id='" + tagid + "' controls='controls' src='" + data.content + "'></audio>");
						$('#' + tagid).mediaelementplayer();
					}
										
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
		
		
	},
	
	/**
	 * Exibir o formulário para adicionar contato
	 * 
	 * @returns void
	 */
	showContactForm: function( combo, voice_id, seg_id ) {
		
		var contact_id = $(combo).find('option:selected').val();
		
		// Exibe o formuláro para edição
		if (contact_id == 'N') {
			$('#form-seg-' + seg_id + '-contato').show();
			
		} else { // Modificar a imagem do contato
			
			$('#form-seg-' + seg_id + '-contato').hide();
			
			var sexo = contact_id.split('-').pop();
			if ( sexo == 'F' )
				$('.voice-img-' + voice_id).attr( 'src', '/simce/assets/img/persona.png' );
			else
				$('.voice-img-' + voice_id).attr( 'src', '/simce/assets/img/person.png' );
			
			// Seleciona os outros combos
			$('.voice-combo-' + voice_id).val(contact_id);
			
		}

	},
			
	/**
	 * Adiciona o contato ao combo
	 * 
	 * @param int voiceid
	 * @returns void
	 */
	addContact: function( voice_id, seg_id ) {

		var nome = $('*[name="contato[' + seg_id + '][nome]"]').val();
		var sexo = $('*[name="contato[' + seg_id + '][sexo]"] option:selected').val();
		
		// Verifica se foi informado o nome do contato
		if (nome.length == 0) {
			SiMCE.errorMessage("Por favor, especifique o nome do contato!");
		} else {
		
			// Atualiza os combos
			$('.voice-combo').append(
				"<option value='" + voice_id + '-' + nome + '-' + sexo + "'>" + nome + "</option>"
			);
			
			// Selecionar o contato nos combos correspondentes
			$('.voice-combo-' + voice_id).val(voice_id + '-' + nome + '-' + sexo);
			
			// Ajusta as imagens dos contatos
			if ( sexo == 'F' )
				$('.voice-img-' + voice_id).attr( 'src', '/simce/assets/img/persona.png' );
			else
				$('.voice-img-' + voice_id).attr( 'src', '/simce/assets/img/person.png' );
			
			// Esconder formulário
			$('#form-seg-' + seg_id + '-contato').hide();
			$('*[name="contato[' + seg_id + '][nome]"]').val('');
			$('*[name="contato[' + seg_id + '][sexo]"]').val(0);
			
		}

	},

	/**
	 * Faz a submissão dos dados do registro
	 * 
	 * @returns void
	 */
	submit: function() {

		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "save/",
			$("#form-registro").serialize() + '&data[obs]=' + encodeURIComponent(tinyMCE.get('data[obs]').getContent()) + '&data[relato]=' + encodeURIComponent(tinyMCE.get('data[relato]').getContent()),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Esconde a janela modal
					$("#app-modal").modal("hide");
				
					// Mensagem para o usuário
					SiMCE.successMessage("Interceptação atualizada com sucesso!");
				
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
	 * Aplica o filtro no grid
	 * 
	 * @param int onlyID
	 * @returns void
	 */
	applyFilter: function( onlyID ) {
		
		// Obtem a configuração atual do grid
		var conf = $('#datagrid').dataTable().fnSettings();

		// Verifica se o filtro é aplicado apartir da tela de registro
		if ( onlyID ) {
			var id = $('*[name="data[id]"]').val();
			conf.sAjaxSource = SiMCE.moduleURL + "list/?" + "&filter[id]=" + id + "&operacao=" + SiMCE.getOperacao();
			setTimeout( function() {
				$('*[name="data[id]"]').val('');
				$('*[name="data[alvo]"]').val('');
				$('*[name="data[contato]"]').val('');
			}, 2000 );
		} else {
			conf.sAjaxSource = SiMCE.moduleURL + "list/?" + $("#form-grid-filter").serialize() + "&operacao=" + SiMCE.getOperacao();
		}
		$('#datagrid').dataTable({ bRetrieve: true }).fnDraw( conf );
		$('.grid-filter-clean').removeClass('disabled');
		
	},
	
	/**
	 * Aplica os filtros de registros
	 * 
	 * @returns void
	 */
	applyRecordsFilter: function() {
		SiMCE.functions.applyFilter( true );
	},
	
	/**
	 * Limpa o filtro no grid
	 * 
	 * @returns void
	 */
	cleanFilter: function() {
		
		// Obtem a configuração atual do grid
		var conf = $('#datagrid').dataTable().fnSettings();
		conf.sAjaxSource = SiMCE.moduleURL + "list/?operacao=" + SiMCE.getOperacao();
		conf._iDisplayStart = 0;
		
		$('#datagrid').dataTable({ bRetrieve: true }).fnDraw( conf );
		$('.grid-filter-clean').addClass('disabled');
		
	},
	
	/**
	 * Exibe o player para a alocação selecionada
	 * 
	 * @returns void
	 */
	showOnlinePlayer: function( element, alocacao, label ) {
	
		// Verifica o status do canal
		if ($(element).attr('src').match(/busy/)) { // Ocupado
			
			var ts = new Date().getTime();

			// Cria o elemento de audio
			SiMCE.functions.player = document.createElement('audio');
			//SiMCE.functions.player.setAttribute('src','http://' + window.location.hostname + ':50550/' + alocacao);
			SiMCE.functions.player.setAttribute('src','/simce/interceptacoes/playOnline/?id='  + alocacao + '&ts=' + ts );
			//SiMCE.functions.player.setAttribute('src','http://25.126.130.211:53890');
			SiMCE.functions.player.controls = false;
			SiMCE.functions.player.loop = false;
			SiMCE.functions.player.autoplay = true;
			SiMCE.functions.player.addEventListener("canplay", function() { 
				$('#audio-placeholder-msg').html('<i>' + label + '</i>');
				$('#audio-placeholder-btn').removeClass('hidden');
			});
			SiMCE.functions.player.addEventListener("loadstart", function() { 
				$('#audio-placeholder-msg').html('<i>Conectando...</i>');
			});
			SiMCE.functions.player.addEventListener("ended", function() { 
				SiMCE.functions.stopOnlinePlayer();
			});
			SiMCE.functions.player.load();
			
			// Adiciona ao placeholder
			$(SiMCE.functions.player).appendTo( $('#audio-placeholder-online') );
			
		} else { // Livre ou com problema
			
			SiMCE.functions.stopOnlinePlayer();
			
		}
		
	},
	
	/**
	 * Para a reprodução online dos canais
	 * 
	 * @returns void
	 */
	stopOnlinePlayer: function() {
	
		// Pausa o áudio
		SiMCE.functions.player.pause();
		
		// Reset do texto
		$('#audio-placeholder-msg').html('<i>Nenhum</i>');
		
		// Remove o botão
		$('#audio-placeholder-btn').addClass('hidden');
		
	},
	
	/**
	 * Exporta os registros da tela em formato ZIP
	 * 
	 * @returns void
	 */
	exportRecords: function() {

		bootbox.confirm("Este procedimento irá exportar todos os registros do filtro atual em um único arquivo e poderá demorar bastante de acordo com o volume de dados. Tem certeza de que deseja continuar?", function(result) {

			if (result === true) {
				// Obtem a configuração atual do grid
				var conf = $('#datagrid').dataTable().fnSettings();
				url = conf.sAjaxSource + "&filter[full]=1&filter[format]=zip";

				// Solicita o carregamento dos registros
				SiMCE.ajaxRequest(
					"POST",
					url,
					{},
					function( data, textStatus, jqXHR ) {
						if ( data.success ) {
							// Inicia o processo de exportação dos registros
							SiMCE.functions.startExport( data.total, data.binds, data.statement );
						}
					}
				);
			}
	
		});
		
		
	},

	/**
 	 * Inicia o processo do backup
 	 * 
 	 * @returns void
 	 */
	startExport: function( total, binds, statement ) {

		var url = SiMCE.moduleURL + "startExport/";
		url += "?operacao=" + SiMCE.getOperacao();
		url += "&total=" + total;
		url += "&binds=" + binds;
		url += "&statement=" + statement;
	
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

	}
	
};
