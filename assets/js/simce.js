/**
 * SiMCE 2 - Javascript Object
 */

var SiMCE = {

	/**
	 * Nome do módulo atual
	 * 
	 * @type string
	 */
	moduleName: null,
			
	/**
	 * URL do módulo atual
	 * 
	 * @type string
	 */
	moduleURL: null,
			
	/**
	 * URL do módulo atual
	 * 
	 * @type string
	 */
	moduleDiv: null,

	/**
	 * Objeto que irá armazenar as funções do módulo atual
	 * 
	 * @type Object
	 */
	functions: null,

	/**
	 * Armazena o último evento disparado no SiMCE
	 * 
	 * @type Object
	 */
	lastEvent: null,

	/**
	 * Carrega um módulo do sistema
	 * 
	 * @param event event
	 * @returns void
	 */
	loadModule: function( event ) {
		
		SiMCE.lastEvent = event;
		
		// Limpa o conteúdo do elemento atual
		if (SiMCE.moduleDiv)
			SiMCE.moduleDiv.empty();

		// Obtem a url do módulo a ser carregada
		var arrURL = event.target.href.split("#");
		SiMCE.moduleName = arrURL[1];
		SiMCE.moduleURL  = "/simce/" + SiMCE.moduleName + "/";
		SiMCE.moduleDiv  = $("#" + SiMCE.moduleName);
		
		//Faz o carregamento
		SiMCE.ajaxRequest( "POST", SiMCE.moduleURL,
			{ operacao: SiMCE.getOperacao() },
			function( data, textStatus, jqXHR ) {
				
				// Atualiza o conteúdo do componente
				SiMCE.moduleDiv.html( data.content );
				
				// Verifica se houve sucesso na transação
				if ( data.success === true) {
					
					//-----------------------------------
					//
					// Executa método onUnload se existir
					//
					//-----------------------------------
					if ( SiMCE.onUnload ) {
						SiMCE.onUnload();
					}

					//-----------------------------------
					//
					// Verifica se existe método functions
					//
					//-----------------------------------
					if ( data.functions ) {
						eval( data.functions );
					} else {
						SiMCE.functions = null;
					}
					
					//-----------------------------------
					//
					// Verifica se existe método onLoad
					//
					//-----------------------------------
					if ( data.onLoad ) {
						eval( data.onLoad );
						SiMCE.onLoad();
					} else {
						SiMCE.onLoad = null;
					}
					
					
					//-----------------------------------
					//
					// Verifica se existe método onUnLoad
					//
					//-----------------------------------
					if ( data.onUnload ) {
						eval( data.onUnload );
					} else {
						SiMCE.onUnload = null;
					}
					
					//-----------------------------------
					//
					// Verifica se existe método functions
					//
					//-----------------------------------
					if ( data.functions ) {
						eval( data.functions );
					} else {
						SiMCE.functions = null;
					}
					
				}
				
			}
		);
		
		
	},

	/**
	 * Faz uma requisição ajax
	 * 
	 * @param string method
	 * @param string url
	 * @param Object opts
	 * @param function callback
	 * @returns void
	 */
	ajaxRequest: function( method, url, opts, callback ) {
		
		// Exibe o loader do ajax
		$('#ajax-loader').show();
		
		$.ajax({
			url:     url,
			type:    method,
			data:    opts,
			cache:   false,
			timeout: 3600000,
			success: function( data, textStatus, jqXHR ) { // Global error handler
		
				// Esconde o loader do ajax
				$('#ajax-loader').hide();
				
				// Se a requisição Ajax foi executada e retornou com sucesso
				if ( data.success === true) {
					callback( data, textStatus, jqXHR );
				} else {
			
					// Se possuir algum conteúdo de erro exibe ao usuário
					if ( data.content )
						SiMCE.moduleDiv.html( data.content );
					
					// Informa o erro para o usuário
					if ( data.error ) {
						SiMCE.errorMessage( data.error );
						callback( data, textStatus, jqXHR );
					} else {
						// Se existir algum modal, remove
						$(".modal-backdrop").remove();
					}
					

			
				}
				
			}
		});
		
	},
	
	/**
	 * Exibe uma mensagem de sucesso
	 * 
	 * @param string msg
	 * @returns void
	 */
	successMessage: function( msg ) {
		$.bootstrapGrowl( msg,{
			type:  "success",
			align: "center"
		});
	},
	
	/**
	 * Exibe uma mensagem de alerta
	 * 
	 * @param string msg
	 * @returns void
	 */
	alertMessage: function( msg ) {
		$.bootstrapGrowl( msg,{
			type:  "warning",
			align: "center"
		});
	},
	
	/**
	 * Exibe uma mensagem de erro
	 * 
	 * @param string msg
	 * @returns void
	 */
	errorMessage: function( msg ) {
		$.bootstrapGrowl( msg,{
			type:  "danger",
			align: "center"
		});
	},

	/**
	 * Aplica os eventos para os grids
	 * 
	 * @returns void
	 */
	applyGridEvents: function() {

		$('#datagrid tbody tr').click( function(e) {

			if ( $(this).hasClass('row_selected') ) {

				$(this).removeClass('row_selected');
				$(".btn-edit").addClass("disabled");
				$(".btn-remove").addClass("disabled");

				SiMCE.functions.data = null;

			} else {

				$('#datagrid').dataTable().$('tr.row_selected').removeClass('row_selected');
				$(this).addClass('row_selected');
				$(".btn-edit").removeClass("disabled");
				$(".btn-remove").removeClass("disabled");

				// Obtem os dados selecionados do grid
				var row = $('#datagrid').dataTable().fnGetPosition(this);
				SiMCE.functions.data = $('#datagrid').dataTable().fnGetData(row);

			}
		});

		$('.paginate_button').addClass( 'btn' ).addClass( 'btn-default' );
		$('.paginate_active').addClass( 'btn' ).addClass( 'btn-default' ).addClass( 'disabled' );
		$('#datagrid_paginate').css('margin', '-20px 15px 20px 0px');
			
	},

	/**
	 * Retorna a configuração padrão dos grids
	 * 
	 * @param {type} pagination
	 * @returns {SiMCE.getGridConfig.Anonym$4}
	 */
	getGridConfig: function( pagination ) {
		
		pagination = pagination || false;
		
		return {
			aaSorting:       [[ 0, "desc" ]],
			bDeferRender:    true,
			bFilter:         false,
			bLengthChange:   false,
			fnDrawCallback:  SiMCE.applyGridEvents,
			iCookieDuration: 60*60*24,
			bProcessing:     true,
			bServerSide:     true,
			bStateSave:      false,
			bPaginate:	 pagination,
			bSort:           false,
			sDom:            "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
			sPaginationType: "full_numbers",
			oLanguage:       {
				sInfo:         (pagination === true) ? "Mostrando de _START_ a _END_ de _TOTAL_ registro(s)" : "_TOTAL_ registro(s) encontrado(s)",
				sEmptyTable:   "Nenhum registro encontrado.",
				sInfoFiltered: " ",
				sInfoEmpty:    " ",
				sLengthMenu:   "Exibindo _MENU_ registros",
				sProcessing:   "Carregando...",
				oPaginate: {
					sFirst:    "<< Primeira",
					sPrevious: "< Anterior",
					sNext:     "Próxima >",
					sLast:     "Última >>"
				}
			}
		};
	
	},

	/**
	 * Informa o ID da operação selecionada
	 * 
	 * @returns int
	 */
	getOperacao: function() {
		return $('*[name="data[operacao]"]').val();
	},

	/**
	 * Recarrega o último evento
	 * 
	 * @returns void
	 */
	reloadEvent: function() {
		SiMCE.loadModule( SiMCE.lastEvent );
	},
	
	/**
	 * Recarrega as operações
	 * 
	 * @returns {undefined}
	 */
	reloadOperacoes: function() {
	
		// Obtem a nova lista de operações
		SiMCE.ajaxRequest( "POST", "/simce/operacoes/comboList/?", null,
			function( data, textStatus, jqXHR ) {
				
				// Remove os elementos atuais
				$('*[name="data[operacao]"').find('option').remove();
				
				// Adiciona os novos
				$.each( data.data, function( index, value ) {
					$('*[name="data[operacao]"').append('<option value="'+value.id+'">'+value.nome+'</option>');
				});
				
				// Recarrega o útimo evento
				SiMCE.reloadEvent();
				
			}
		);
		
	},
	
	/**
	 * Obtem o conteúdo do arquivo e adiciona no 
	 * elemento informado
	 * 
	 * @param event evt
	 * @param string targetClass
	 * @param string previewClass
	 * @returns void
	 */
	getFileContent: function( evt, targetClass, previewClass ) {
		
		var f = evt.target.files[0]; 

		if (f) {
			var r = new FileReader();
			r.onload = function(e) { 
				
				// Recebe o arquivo e divide em chunks de 256kb
				var contents = e.target.result;
				var chunks = contents.match(/.{1,102400}/g);
				
				// Atualiza o preview se existir
				if (previewClass)
					$('.' + previewClass).attr("src", contents );
				
				// Gera o ID na sessão para o arquivo
				var fileID = SiMCE.randomString(20);
				
				// Atualiza o conteúdo com o ID do arquivo na sessão
				var target = $('.' + targetClass);
				if (targetClass)
					$('.' + targetClass).val( fileID );
				
				// Mostra a janela modal
				$("#app-upload").modal({
					remote:   '/simce/uploadForm/',
					backdrop: "static",
					show:     true,
					keyboard: false
				}).on('hidden.bs.modal', function () {
					$(this).removeData('bs.modal');
				});
				
				// Faz o envio dos dados para o servidor
				SiMCE.updateFileUpload( 0, chunks, fileID );
				
			};
			r.readAsDataURL(f);
		}
	
	},
	
	/**
	 * Faz o envio de cada um dos pedaços do arquivo 
	 * 
	 * @param int index
	 * @param mixed dataArray
	 * @param string fileID
	 * @returns void
	 */
	updateFileUpload: function( index, chunks, fileID ) {
		
		SiMCE.ajaxRequest(
			"POST",
			"/simce/upload/",
			{ id: fileID, data: chunks[ index ] },
			function( data, textStatus, jqXHR ) {
				
				if ( data.success === true ) {

					// Atualiza o progresso
					var percPerChunk = Math.floor( 100 / chunks.length );
					var perc = percPerChunk * ( index + 1);
					$('.progress-bar').attr('aria-valuenow', perc ).css('width', perc + '%');

					// Verifica se existe mais algum chunk
					if ( chunks[ index + 1 ] ) {
						SiMCE.updateFileUpload( index + 1, chunks, fileID );
					} else { // Caso ja tenha enviado todos
						
						// Esconde a janela modal
						$("#app-upload").modal("hide");
						
					}
					
				}
			}
		);
	
	},
	
	/**
	 * Cria uma string randomica
	 * 
	 * @param string len
	 * @param string charSet
	 * @returns string
	 */
	randomString: function (len, charSet) {
		charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var randomString = '';
		for (var i = 0; i < len; i++) {
			var randomPoz = Math.floor(Math.random() * charSet.length);
			randomString += charSet.substring(randomPoz,randomPoz+1);
		}
		return randomString;
	},
	
	/**
	 * Atualiza e verifica informações da sessão do usuário
	 * 
	 * @returns void
	 */
	processUserEvents: function() {
		
		SiMCE.ajaxRequest(
			"POST",
			"/simce/user_events/",
			{ },
			function( data, textStatus, jqXHR ) {

				// Processa as informações retornadas
				if ( data.success === true ) {

					// Verifica se existem novas ligações
					if ( data.newCalls ) {
						$('#notification-number').html( data.newCalls );
						$('#notification-divider').removeClass('hidden');
						$('#notification-calls').removeClass('hidden').html(
							'<span class="cus-information"></span> Existem ' + data.newCalls + ' novas interceptações. ' + data.newCallsMsg
						);
					} else {
						$('#notification-number').empty();
						$('#notification-divider').addClass('hidden');
						$('#notification-calls').addClass('hidden');
					}

					// Obtem o changelog
					SiMCE.ajaxRequest(
						"POST",
						"/simce/changelog/",
						{ },
						function( data, textStatus, jqXHR ) {
							
							for (version in data.log) {
								
								var release   = version.split(" ");
								var strCookie = "SiMCE_v_" + release[0];
								if (data.new === false) {
									$("#new-version-tag").hide();
								} else {
									$("#new-version-tag").show();
								}
								break;
								
							}
							
						}
					);

					// Agenda a próxima execução
					setTimeout( SiMCE.processUserEvents, 15000 );
					
				}
			}
		);
	
	},
	
	/**
	 * Exibe o formulário de perfil so usuário
	 * 
	 * @returns void
	 */
	showUserProfile: function() {
	
		var url = '/simce/user_profile';
	
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
	saveUserProfile: function() {

		// Reset do formulário
		$('.has-error').removeClass("has-error");

		SiMCE.ajaxRequest(
			"POST",
			'/simce/save_user_profile/',
			$("#form-user-profile").serialize(),
			function( data, textStatus, jqXHR ) {
				
				// Verifica se a requisição foi executada com sucesso
				if ( data.success === true ) {
				
					// Esconde a janela modal
					$("#app-modal").modal("hide");
				
					// Mensagem para o usuário
					SiMCE.successMessage("Perfil atualizado com sucesso!");
				
				} else { // Em caso de erro
					$.each( data.fields, function( index, key ) {
						$('*[name="data[' + key + ']"]').parent().addClass("has-error");
					});
				}
				
			}
		);

	},
	
	/**
	 * Exibe o changelog
	 * 
	 * @returns void
	 */
	showChangeLog: function() {
	
		var url = '/simce/changelog/?details=1';
	
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
	 * Verifica o ID informado e busca pela operação. Caso encontre, direciona
	 * para a tela de registros
	 * 
	 * @returns void
	 */
	findID: function(event) {
		
		event.preventDefault();
		
		var id = $('*[name="data[id]"]').val();
		if (id) {

			// Obtem os detalhes da operação
			SiMCE.ajaxRequest(
				"POST",
				'/simce/find_id/',
				'id=' + id ,
				function( data, textStatus, jqXHR ) {

					// Verifica se a requisição foi executada com sucesso
					if ( data.success === true ) {

						if (data.record.id == 0) {
							SiMCE.errorMessage("ID não encontrado.");
							$('*[name="data[id]"]').val('');
						} else {
							
							// Busca a operação
							var exists = false;
							$('.operacao-select  option').each(function(){
								if (this.value == data.record.operacoes_id) {
									$('.operacao-select').val( data.record.operacoes_id );
									exists = true;
								}
							});
							if (exists === false) {
								SiMCE.errorMessage("ID não encontrado.");
								$('*[name="data[id]"]').val('');
							}

							// Carrega a tela de interceptações
							if (SiMCE.functions.applyRecordsFilter)
								SiMCE.functions.applyRecordsFilter();
							else
								$('a[href="#interceptacoes"]').tab('show');
							
						}

					} else { // Em caso de erro

						// TODO

					}

				}
			);
			
			
			
		}
		
		return false;
		
	}
	
};
