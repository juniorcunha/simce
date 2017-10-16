/**
 * 
 * Evento que é executado no SiMCE após
 * o carregamento do módulo.
 * 
 * @returns void
 */

SiMCE.onLoad = function() {
	
	// Cria o grid
	var cfg = SiMCE.getGridConfig(true);
	cfg.bSort = true;
	cfg.aoColumns = [
		{ mData: "id",            sWidth: "auto", "bSortable": false },
		{ mData: "tipo",          sWidth: "60px", "bSortable": false },
		{ mData: "data",          sWidth: "100px", "bSortable": true },
		{ mData: "status",        sWidth: "120px", "bSortable": false },
		{ mData: "alvo",          sWidth: "auto", "bSortable": false },
		{ mData: "contatos",      sWidth: "auto", "bSortable": false },
		{ mData: "grafico",       sWidth: "100px", "bSortable": false },
		{ mData: "tamanho",       sWidth: "100px", "bSortable": false },
                { mData: "acoes",         sWidth: "auto", "bSortable": false }
	];
	// Sobreescreve a função de callback
	cfg.fnDrawCallback = function() {
		
		/*$('#datagrid tbody tr').click( function(e) {

			// Obtem os dados selecionados do grid
			var row = $('#datagrid').dataTable().fnGetPosition(this);
			SiMCE.functions.data = $('#datagrid').dataTable().fnGetData(row);
			
			// Executa a função da janela modal
			SiMCE.functions.showDetails( SiMCE.functions.data.id );
				
		}).hover( function() {
			$(this).css('cursor', 'pointer');
			$(this).attr('alt', 'Clique para obter mais detalhes');
			$(this).attr('title', 'Clique para obter mais detalhes');
		}, function() {
			$(this).css('cursor', 'auto');
		});*/
		
		$('.paginate_button').addClass( 'btn' ).addClass( 'btn-default' );
		$('.paginate_active').addClass( 'btn' ).addClass( 'btn-default' ).addClass( 'disabled' );
		$('#datagrid_paginate').css('margin', '-20px 15px 20px 0px');

		// Adiciona o botão de tamanho de página
		if ($('.table-page').length == 0) {
			$('#datagrid_paginate').prepend('&nbsp;|&nbsp;');
			$('#datagrid_paginate').prepend('<select class="form-control input-sm table-page" style="width: 70px;" onchange="var conf = $(\'#datagrid\').dataTable().fnSettings(); conf._iDisplayLength = $(this).val(); $(\'#datagrid\').dataTable({ bRetrieve: true }).fnDraw( conf );"><option value="10">10</option><option value="20">20</option><option value="50">50</option><option value="100">100</option></select>');
			$('#datagrid_paginate').prepend('Tamanho da Página:&nbsp;');
		}
	
		// Adiciona o botão de recarregar
		if ($('.table-refresh').length == 0) {
			$('#datagrid_paginate').append('&nbsp;|&nbsp;');
			$('#datagrid_paginate').append('<a tabindex="0" class="table-refresh btn btn-default" onclick="$(\'#datagrid\').dataTable({ bRetrieve: true }).fnDraw();" style="height: 29px;"><span class="cus-arrow-refresh"></span></a>');
		}
		
                // Habilita o select picker
                $('.selectpicker').selectpicker({ width: '55px', style: 'btn-link' }).on('change',function(e) {
                    var opt = $("option:selected", this).val().split("-");
                    var status = opt[0];
                    var row_id = opt[1];
                    
                    // Faz o update do registro
                    SiMCE.ajaxRequest(
                        "POST",
                        SiMCE.moduleURL + "updateStatus/",
                        { id: row_id, status: status },
                        function( data, textStatus, jqXHR ) {
                            var conf = $('#datagrid').dataTable().fnSettings();
                            $('#datagrid').dataTable({ bRetrieve: true }).fnDraw( conf );
                        }
                    );
                    
                });

		// Habilita os tooltip
		$('[data-toggle="popover"]').each( function(idx) {
			try {
				$( this ).popover({ html: true, content: window.atob($(this).attr('tip')) });
			} catch (e) {
				// Silently ignore it
			}
		});
		
	};
	cfg.sAjaxSource = SiMCE.moduleURL + "list/?operacao=" + SiMCE.getOperacao();
	var id = $('*[name="data[id]"]').val();
	if (id)
		cfg.sAjaxSource += "&filter[id]=" + id;
	var alvo = $('*[name="data[alvo]"]').val();
	if (alvo)
		cfg.sAjaxSource += "&filter[alvos_id][]=" + alvo;
	var contato = $('*[name="data[contato]"]').val();
	if (contato)
		cfg.sAjaxSource += "&filter[contatos_id][]=" + contato;
	
	
	//cfg.sScrollY = "300px";
	//cfg.bScrollCollapse = true;
	$('#datagrid').dataTable(cfg);
	
};
