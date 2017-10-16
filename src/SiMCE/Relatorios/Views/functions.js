SiMCE.functions = {

	cancelDownload: 0,
	
	/**
	 * Armazena os dados do relatório
	 * 
	 * @var Object
	 */
	reportData: {},
	
	/**
	 * Carrega o menu de relatórios
	 * 
	 * @returns void
	 */
	loadMenu: function() {
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "menu/",
			{},
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					$("#form-relatorio").html( data.content );

				}
				
			}
		);
		
	},

	/**
	 * Função responsável por carregar o filtro do relatório selecionado
	 * 
	 * @returns void
	 */
	
	loadFilter: function() {
		
		// Carrega o filtro da opção selecionada
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "filter/",
			$("#form-relatorio").serialize() + "&operacao=" + SiMCE.getOperacao(),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					$("#form-relatorio").html( data.content );

				}
				
			}
		);
		
	},
	
	/**
	 * Função responsável por carregar o filtro do relatório selecionado
	 * 
	 * @returns void
	 */
	
	loadReport: function() {
		
		var opts = $("#form-relatorio").serialize();
		
		// Carrega a tela de "aguardando"...
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "loading/",
			{},
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Adiciona a tela ao formulário
					$("#form-relatorio").html( data.content );
					
					// Faz a requisição que irá processar o relatório
					SiMCE.ajaxRequest(
						"POST",
						SiMCE.moduleURL + "process/",
						opts + "&operacao=" + SiMCE.getOperacao(),
						function( data, textStatus, jqXHR ) {

							// Verifica se a requisição foi executada com sucesso
							if ( data.success === true ) {

								$("#form-relatorio").fadeOut( function() {
									$(this).html( data.content );
								}).fadeIn();

							}
							
						}
					);
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
			"/simce/interceptacoes/targets/",
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
			"/simce/interceptacoes/contacts/",
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
	
	//=============================================
	//
	// Relatório de Operação
	//
	//=============================================
	
	/**
	 * Gera o PDF da Operação
	 * 
	 * @returns void
	 */
	genPDFOperationReport: function() {
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "gen_operacao_report/",
			$("#form-relatorio").serialize() + "&operacao=" + SiMCE.getOperacao(),
			function( data, textStatus, jqXHR ) {

				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {

					window.open( data.file, "SiMCE - Relatório de Operação" );

				}

			}
		);
	
	},

	//=============================================
	//
	// Relatório de Acesso
	//
	//=============================================
	
	/**
	 * Gera o PDF de Acesso
	 * 
	 * @returns void
	 */
	genPDFAccessReport: function() {
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "gen_access_report/",
			$("#form-relatorio").serialize() + "&operacao=" + SiMCE.getOperacao(),
			function( data, textStatus, jqXHR ) {

				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {

					window.open( data.file, "SiMCE - Relatório de Acesso" );

				}

			}
		);
	
	},

	//=============================================
	//
	// Relatório de Canais
	//
	//=============================================
	
	/**
	 * Gera o PDF de Canais
	 * 
	 * @returns void
	 */
	genPDFChannelReport: function() {
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "gen_channel_report/",
			$("#form-relatorio").serialize() + "&operacao=" + SiMCE.getOperacao(),
			function( data, textStatus, jqXHR ) {

				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {

					window.open( data.file, "SiMCE - Relatório de Canais" );

				}

			}
		);
	
	},

	//=============================================
	//
	// Relatório de Desvios
	//
	//=============================================
	
	/**
	 * Gera o PDF de Desvios
	 * 
	 * @returns void
	 */
	genPDFCallForwardReport: function() {
		
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "gen_call_forward_report/",
			$("#form-relatorio").serialize() + "&operacao=" + SiMCE.getOperacao(),
			function( data, textStatus, jqXHR ) {

				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {

					window.open( data.file, "SiMCE - Relatório de Desvios" );

				}

			}
		);
	
	},

	
	//=============================================
	//
	// Relatório Judicial
	//
	//=============================================
	
	/**
	 * Mostra o formulário para inserção de registros
	 * 
	 * @returns void
	 */
	showRecordForm: function() {
	
		var url = SiMCE.moduleURL + "show_record_form/?operacao=" + SiMCE.getOperacao();
		
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
	 * Faz a submissão dos dados do registro
	 * 
	 * @returns void
	 */
	addRecords: function() {

		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "get_records/",
			$("#form-record").serialize() + "&operacao=" + SiMCE.getOperacao(),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Esconde a janela modal
					$("#app-modal").modal("hide");
				
					// Adiciona o conteúdo no editor
					tinyMCE.get('data[conteudo]').insertContent( data.content );
				
				}
				
			}
		);
	
	},
	
	/**
	 * Mostra o formulário para inserção de alvos
	 * 
	 * @returns void
	 */
	showTargetForm: function() {
	
		var url = SiMCE.moduleURL + "show_target_form/?operacao=" + SiMCE.getOperacao();
		
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
	 * Faz a submissão dos dados do registro
	 * 
	 * @returns void
	 */
	addTargets: function() {

		var arrTargets = new Array();
		$('.cb-form-filter:checked').each( function() {
			arrTargets.push( $(this).val() );
		});

		if (arrTargets.length == 0) {
			
			// Esconde a janela modal
			$("#app-modal").modal("hide");
			
			return;
			
		}

		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "get_targets/",
			{
				alvos:    arrTargets,
				operacao: SiMCE.getOperacao()
			},
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Esconde a janela modal
					$("#app-modal").modal("hide");
				
					// Adiciona o conteúdo no editor
					tinyMCE.get('data[conteudo]').insertContent( data.content );
				
				}
				
			}
		);
	
	},
	
	/**
	 * Adiciona a rede de relacionamentos
	 * 
	 * @returns void
	 */
	addNetwork: function() {
	
		// Exibe
		$("#app-modal").modal({
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
	
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "get_graph/",
			{
				operacao: SiMCE.getOperacao()
			},
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Esconde a janela modal
					$("#app-modal").modal("hide");
				
					// Adiciona o conteúdo no editor
					tinyMCE.get('data[conteudo]').insertContent( data.content );
				
				}
				
			}
		);
		
	},	
	
	/**
	 * Salva o relatório
	 * 
	 * @returns void
	 */
	saveJudicialReport: function() {

		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "save_judicial_report/",
			$("#form-relatorio").serialize() + "&operacao=" + SiMCE.getOperacao() + '&data[conteudo]=' + encodeURIComponent(tinyMCE.get('data[conteudo]').getContent()),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Mensagem para o usuário
					SiMCE.successMessage("Relatório Judicial salvo com sucesso!");
				
				}
				
			}
		);
	
	},
	
	/**
	 * Gera o PDF
	 * 
	 * @returns void
	 */
	genPDFJudicialReport: function() {

		$("#loadingReport").modal({show:true,keyboard:false});

		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "gen_judicial_report/",
			$("#form-relatorio").serialize() + "&operacao=" + SiMCE.getOperacao() + '&data[conteudo]=' + encodeURIComponent(tinyMCE.get('data[conteudo]').getContent()),
			function( data, textStatus, jqXHR ) {

				$("#loadingReport").modal('hide');

				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {

					window.open( data.file, "SiMCE - Relatório Judicial" );

				}

			}
		);
	
	}
	
};
