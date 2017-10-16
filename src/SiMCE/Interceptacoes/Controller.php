<?php

/**
 * Controlador responsável por manipular as interceptações
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Interceptacoes;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		$app->match( $prefix ,                 array(__CLASS__,'index')   );
		$app->match( $prefix . "list/" ,       array(__CLASS__,'getList') );
		$app->match( $prefix . "online/" ,     array(__CLASS__,'online')  );
		$app->match( $prefix . "details/",     array(__CLASS__,'details') );
		$app->match( $prefix . "play/",        array(__CLASS__,'play')    );
		$app->match( $prefix . "playOnline/",  array(__CLASS__,'playOnline'));
		$app->match( $prefix . "save/",        array(__CLASS__,'save')    );
		$app->match( $prefix . "download/",    array(__CLASS__,'download'));
		$app->match( $prefix . "targets/",     array(__CLASS__,'targets'));
		$app->match( $prefix . "contacts/",    array(__CLASS__,'contacts'));
		$app->match( $prefix . "updateStatus/",array(__CLASS__,'updateStatus'));
		$app->match( $prefix . "fileDownload/",array(__CLASS__,'fileDownload'));
		$app->match( $prefix . "startExport/", array(__CLASS__,'startExport'));
		$app->match( $prefix . "statusExport/",array(__CLASS__,'statusExport'));
		$app->match( $prefix . "cancelExport/",array(__CLASS__,'cancelExport'));
	}
	
	/**
	 * Tela inicial
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function index( \Silex\Application $app ) {
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'error'     => false,
				'content'   => $app['twig']->render( basename(__DIR__) . '/Views/index.html' ),
				'onLoad'    => $app['twig']->render( basename(__DIR__) . '/Views/onLoad.js' ),
				'functions' => $app['twig']->render( basename(__DIR__) . '/Views/functions.js' ),
				'onUnload'  => false
			)
		);
			
	}
	
	/**
	 * Retorna a lista de registros cadastrados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getList(\Silex\Application $app ) {
		
		// Obtem o filtro caso tenha sido aplicado
		$filter = $app['request']->get('filter');
		
		// Obtem os alvos que o usuário tem permissão de visualizar
		$targets = $app['user']->getTargetsByAction( $app['request']->get('operacao'), "ACTION_INTERCEPTION_" );
		
		// Especifica o arquivo zip que será criado caso seja solicitado
		//$zipFile = SIMCE_CACHE_DIR . "registros-" . $app['request']->get('operacao') . "-" . microtime(true) . "-" . uniqid("", true) . ".zip";
		
		//==================================
		//
		// Cria o corpo padrão da query
		//
		//==================================
		$statement = null;
		$binds = array();
		if ($app['user']->isAdmin()) {
			$statement         = " unidades_id = :unidade AND operacoes_id = :operacao ";
			$binds["unidade"]  = $app['user']->getUnidade();
			$binds["operacao"] = $app['request']->get('operacao');
		} else {
			$statement         = " unidades_id = :unidade AND operacoes_id = :operacao AND alvos_id IN ( " . implode(",", $targets) . " ) ";
			$binds["unidade"]  = $app['user']->getUnidade();
			$binds["operacao"] = $app['request']->get('operacao');
		}
		
		//==================================
		//
		// Aplica o filtro
		//
		//==================================
		if ($filter !== null) {
			
			// ID
			if (!empty($filter['id'])) {
				$statement .= " AND id = :id ";
				$binds['id'] = $filter['id'];
			}
			
			// Data
			if (!empty($filter['data'])) {
				if (!empty($filter['data']['start'])) {
					$arr = explode('/', $filter['data']['start']);
					$statement .= " AND DATE(data) >= :start ";
					$binds['start'] = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
				}
				if (!empty($filter['data']['end'])) {
					$arr = explode('/', $filter['data']['end']);
					$statement .= " AND DATE(data) <= :end ";
					$binds['end'] = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
				}
			}
			
			// Tipo
			if (!empty($filter['tipo'])) {
				$statement .= " AND tipo IN ( '" . implode("','", $filter['tipo']) . "' ) ";
			}

			// Classificacão
			if (!empty($filter['classificacao'])) {
				$statement .= " AND classificacao IN ( '" . implode("','", $filter['classificacao']) . "' ) ";
			}
			
			// Estado
			if (!empty($filter['estado'])) {
				$statement .= " AND estado IN ( '" . implode("','", $filter['estado']) . "' ) ";
			}
			
			// Alvos
			if (!empty($filter['alvos_id'])) {
				$statement .= " AND alvos_id IN ( '" . implode("','", $filter['alvos_id']) . "' ) ";
			}
			
			// Interlocutores
			if (!empty($filter['contatos_id'])) {
				$statement .= " AND id IN ( select DISTINCT registros_id FROM segmentos WHERE voiceid_id IN ( select id from voiceid where contatos_id in ( '" . implode("','", $filter['contatos_id']) . "' ))) ";
			}
			
			// Palavras
			if (!empty($filter['palavra'])) {
				$statement .= " AND ( id IN ( SELECT DISTINCT registros_id FROM segmentos WHERE transcricao RLIKE '{$filter['palavra']}' ) OR " .
				              "     ( relato RLIKE '" . htmlentities($filter['palavra'], ENT_QUOTES, "UTF-8") . "' ) OR " . 
							  "     ( observacoes RLIKE '" . htmlentities($filter['palavra'], ENT_QUOTES, "UTF-8") . "' )) ";
			}
			
			// Breve Relato
			if (!empty($filter['relato'])) {
				$statement .= " AND LENGTH(relato) > 0 ";
			}
			
			// Observações 
			if (!empty($filter['observacao'])) {
				$statement .= " AND LENGTH(observacoes) > 0 ";
			}
			
			// Transcrição 
			if (!empty($filter['transcricao'])) {
				$statement .= " AND id IN ( SELECT DISTINCT registros_id FROM segmentos WHERE LENGTH(transcricao) > 0 ) ";
			}
			
			// Tamanho Início
			if (!empty($filter['tamanho_inicio'])) {
				if ($filter['tamanho_tipo'] == 0) { // Áudio
					$statement .= " AND substring( attr, locate( ':', attr ) + 1, locate( ',', attr ) - locate( ':', attr) -1 ) >= TIME_TO_SEC('{$filter['tamanho_inicio']}') ";
				} else {
					$statement .= " AND tamanho >= :tamanho_inicio ";
					$binds['tamanho_inicio'] = $filter['tamanho_inicio'];
				}
			}
			
			// Tamanho Fim
			if (!empty($filter['tamanho_fim'])) {
				if ($filter['tamanho_tipo'] == 0) { // Áudio
					$statement .= " AND substring( attr, locate( ':', attr ) + 1, locate( ',', attr ) - locate( ':', attr) -1 ) <= TIME_TO_SEC('{$filter['tamanho_fim']}') ";
				} else {
					$statement .= " AND tamanho <= :tamanho_fim ";
					$binds['tamanho_fim'] = $filter['tamanho_fim'];
				}
			}
			
		}
		
		//==================================
		//
		// Adiciona ordenação
		//
		//==================================
		if (empty($app["request"]->get("sSortDir_0")))
			$statement .= " ORDER BY data DESC ";
		else
			$statement .= " ORDER BY data " . $app["request"]->get("sSortDir_0") . " ";
		
		//===============================================
		//
		// Executa a query com os parametros informados
		//
		//===============================================
		$obj = false;
		if (empty($filter["full"])) {
			$obj = \SiMCE\Classes\Registros::getByPage(
				$statement,
				$binds,
				$app["request"]->get("iDisplayStart"),
				$app["request"]->get("iDisplayLength")
			);
		} else {
			$obj = \SiMCE\Classes\Registros::getByPage(
				$statement,
				$binds,
				$app["request"]->get("iDisplayStart"),
				$app["request"]->get("iDisplayLength"),
				false,
				1
			);
		}
		
		// Formata o conteúdo
		foreach( $obj->rows as &$row ) {
			
			$aContatos = array();
			$uContatos = array();
			
			// Adicona o nome do alvo
			$row['alvo'] = \SiMCE\Classes\Alvos::getByID( $row['alvos_id'], true )->get('nome');
			
			// Classificação
			$row['status'] = '<center>';

                        // Seletor de status
			$row['status'] = "
                            <select class='selectpicker'>
                                <option data-content='<i class=\"cus-flag-red\" alt=\"Alta Prioridade\" title=\"Alta Prioridade\"></i>&nbsp;' " . (($row['classificacao'] == \SiMCE\Classes\Registros::PRIORITY_HIGH) ? "selected" : "") . ">" . \SiMCE\Classes\Registros::PRIORITY_HIGH . "-" . $row['id'] . "</option>
                                <option data-content='<i class=\"cus-flag-yellow\" alt=\"Média Prioridade\" title=\"Média Prioridade\"></i>&nbsp;' " . (($row['classificacao'] == \SiMCE\Classes\Registros::PRIORITY_MEDIUM) ? "selected" : "") . ">" . \SiMCE\Classes\Registros::PRIORITY_MEDIUM . "-" . $row['id'] . "</option>
                                <option data-content='<i class=\"cus-flag-green\" alt=\"Baixa Prioridade\" title=\"Baixa Prioridade\"></i>&nbsp;' " . (($row['classificacao'] == \SiMCE\Classes\Registros::PRIORITY_LOW) ? "selected" : "") . ">" . \SiMCE\Classes\Registros::PRIORITY_LOW . "-" . $row['id'] . "</option>
                                <option data-content='<i class=\"cus-flag-blue\" alt=\"Sem Prioridade\" title=\"Sem Prioridade\"></i>&nbsp;' " . (($row['classificacao'] == \SiMCE\Classes\Registros::PRIORITY_NONE) ? "selected" : "") . ">" . \SiMCE\Classes\Registros::PRIORITY_NONE . "-" . $row['id'] . "</option>
                            </select>";

			switch ($row['classificacao']) {
				case \SiMCE\Classes\Registros::PRIORITY_HIGH:
						//$row['status'] .= '<i class="cus-flag-red" alt="Alta Prioridade" title="Alta Prioridade"></i>&nbsp;';
						$row['classificacao_cache'] = '<i class="cus-flag-red" alt="Alta Prioridade" title="Alta Prioridade"></i>&nbsp;Alta Prioridade';
						break;
				case \SiMCE\Classes\Registros::PRIORITY_MEDIUM:
						//$row['status'] .= '<i class="cus-flag-yellow" alt="Média Prioridade" title="Média Prioridade"></i>&nbsp;';
						$row['classificacao_cache'] = '<i class="cus-flag-yellow" alt="Média Prioridade" title="Média Prioridade"></i>&nbsp;Média Prioridade';
						break;
				case \SiMCE\Classes\Registros::PRIORITY_LOW:
						//$row['status'] .= '<i class="cus-flag-green" alt="Baixa Prioridade" title="Baixa Prioridade"></i>&nbsp;';
						$row['classificacao_cache'] = '<i class="cus-flag-green" alt="Baixa Prioridade" title="Baixa Prioridade"></i>&nbsp;Baixa Prioridade';
						break;
				default:
						//$row['status'] .= '<i class="cus-flag-blue" alt="Sem Prioridade" title="Sem Prioridade"></i>&nbsp;';
						$row['classificacao_cache'] = '<i class="cus-flag-blue" alt="Sem Prioridade" title="Sem Prioridade"></i>&nbsp;Sem Prioridade';
						break;
			}

			// Estado
			switch ($row['estado']) {
				case \SiMCE\Classes\Registros::STATUS_VIEWED:
						$row['status'] .= '<i class="cus-table-go" alt="Interceptação Visualizada" title="Interceptação Visualizada"></i>&nbsp;';
						$row['estado_cache'] = '<i class="cus-table-go" alt="Interceptação Visualizada" title="Interceptação Visualizada"></i>&nbsp;Interceptação Visualizada';
						break;
				default:
						$row['status'] .= '<i class="cus-new" alt="Nova Interceptação" title="Nova Interceptação"></i>&nbsp;';
						$row['estado_cache'] = '<i class="cus-new" alt="Nova Interceptação" title="Nova Interceptação"></i>&nbsp;Nova Interceptação';
						break;
			}
			
			// Informações adicionais do estado
			if (!empty($row['relato'])) { // Breve relato
				$row['status'] .= '<i class="cus-pencil-go" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover focus" tip="' . base64_encode($row['relato']) . '"></i>&nbsp;';
				$row['estado_cache'] = '<i class="cus-pencil-go" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover focus" tip="' . base64_encode($row['relato']) . '"></i>&nbsp;Com Breve Relato';
			}
			if (!empty($row['observacoes'])) { // Observações
				$row['status'] .= '<i class="cus-report-go" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover focus" tip="' . base64_encode($row['observacoes']) . '"></i>&nbsp;';
				$row['estado_cache'] = '<i class="cus-report-go" data-container="body" data-toggle="popover" data-placement="top" data-trigger="hover focus" tip="' . base64_encode($row['observacoes']) . '"></i>&nbsp;Com Observações';
			}
			
			// Formatação para Áudio
			if ($row['tipo'] == \SiMCE\Classes\Recursos::TYPE_AUDIO) {
				
				//if (!empty($filter['format']) && $filter['format'] == 'zip') {
					
					//$zReg = \SiMCE\Classes\Registros::getByID( $row['id'], true );
					//system("/usr/bin/zip -jrv $zipFile " . $zReg->getFilePath() . " >/dev/null 2>/dev/null");
					//system("/usr/bin/zip -jrv $zipFile " . $zReg->getImagePath( true ) . " >/dev/null 2>/dev/null");
					//unset( $zReg );
					
				//}
				
				
				// Adiciona os contatos
				$isTranscribed = false;
				$aContatos = array();
				$uContatos = array();
				$aSegmentos = \SiMCE\Classes\Segmentos::getByQuery(
					" registros_id = :id ",
					array( "id" => $row["id"] ),
					true
				);
				foreach( $aSegmentos as $seg ) {
					if ($seg->bean->voiceid->contatos_id !== null)
						$aContatos[] = $seg->bean->voiceid->contatos->nome;
					else
						$uContatos[] = $seg->bean->voiceid->id;
					// Verifica transcrição
					if (!empty($seg->bean->transcricao))
						$isTranscribed = true;
				}
				sort($aContatos); $aContatos = array_unique($aContatos);
				sort($uContatos); $uContatos = array_unique($uContatos);
				if (isset($row['attr']->voiceid) && $row['attr']->voiceid == 0) {
					$row['contatos'] = "Aguardando processo de reconhecimento de voz...";
				} else if (isset($row['attr']->voiceid) && $row['attr']->voiceid == 1) {
					$row['contatos'] = "Processando reconhecimento de voz...";
				} else {
					if (count($uContatos))
						$aContatos[] = count($uContatos) . " Desconhecido(s)";
					if (!count($aContatos))
						$aContatos[] = "Nenhum segmento de voz identificado";
					$row['contatos'] = implode(", ", $aContatos);
				}
				
				// Define o tipo
				$row['tipo_orig'] = $row['tipo'];
				$row['tipo'] = '<center><div id="audio-placeholder-grid-' . $row['id'] . '" style="cursor: pointer;" onclick="SiMCE.functions.addMiniAudioPlayer(\'audio-placeholder-grid-' . $row['id'] . '\', \'audio-grid-' . $row['id'] . '\', \'/simce/interceptacoes/play/?grid=1&id=' . $row['id'] . '\',event);"><i class="cus-music" alt="Interceptação de Áudio - Clique para ouvir" title="Interceptação de Áudio - Clique para ouvir"></i>&nbsp;</div></center>';
				//$row['tipo'] = '<center><i class="cus-music" alt="Interceptação de Áudio" title="Interceptação de Áudio"></i>&nbsp;</center>';
				
				// Tamanho em formato humano
				$base = log($row['tamanho']) / log(1024);
				$suffixes = array('B', 'Kb', 'Mb', 'Gb', 'Tb');   
				$size = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
				
				// Tempo em formato humano
				$time = @gmdate("H:i:s", $row['attr']->tempo%86400);
				
				// Ajusta o valor
				$row['tamanho'] = $time . " (" . $size . ")";
				
				// Cache
				$row['tamanho_cache'] = $size;
				$row['tempo_cache'] = $time;
				
				// Adiciona flag de transcrição
				if ($isTranscribed !== false) {
					$row['status'] .= '<i class="cus-user-go" alt="Com Transcrição" title="Com Transcrição"></i>&nbsp;';
					$row['estado_cache'] = '<i class="cus-user-go" alt="Com Transcrição" title="Com Transcrição"></i>&nbsp;Com Transcrição';
				}
			
				// Adiciona gráfico
				$tReg = \SiMCE\Classes\Registros::getByID( $row['id'], true );
				$row['grafico'] = "<center><img src='" . $tReg->getImagePath() . "' style='height: 40px; width: 200px;'> | <small>" . $tReg->get('identificador') . "</small></center>";
				unset( $tReg );
				
			}
			
			// Formatação para GSM
			if ($row['tipo'] == \SiMCE\Classes\Recursos::TYPE_GSM) {
				
				// Adiciona os contatos
				$row['contatos'] = "N/A";
				
				// Gráfico
				$row['grafico'] = "N/A";
				
				// Define o tipo
				$row['tipo_orig'] = $row['tipo'];
				$row['tipo'] = '<center><i class="cus-email" alt="Interceptação de SMS" title="Interceptação de SMS"></i>&nbsp;</center>';
				
				// Tamanho em formato humano
				$base = log($row['tamanho']) / log(1024);
				$suffixes = array('B', 'Kb', 'Mb', 'Gb', 'Tb');   
				$size = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
				
				// Ajusta o valor
				$row['tamanho'] = $size;
				
				// Cache
				$row['tamanho_cache'] = $size;
				$row['conteudo'] = $row['attr']->conteudo;
								
			}
			
			// Formatação para Dados
			if ($row['tipo'] == \SiMCE\Classes\Recursos::TYPE_DATA) {
				
				// Adiciona os contatos
				$row['contatos'] = "N/A";

				// Propriedades
				if ($row['attr']->type == 'webs') { // Acesso WEB
					
					if (preg_match("/image/", $row['attr']->data->content_type)) { // Imagem
						$row['grafico'] = '<center><i class="cus-image" alt="Imagem" title="Imagem"></i>&nbsp;';
					} else if (preg_match("/(video)/", $row['attr']->data->content_type)) { // Videos
						$row['grafico'] = '<center><i class="cus-film" alt="Vídeo" title="Vídeo"></i>&nbsp;';
					} else if (preg_match("/(css|javascript)/", $row['attr']->data->content_type)) { // Estilos e JS
						$row['grafico'] = '<center><i class="cus-page-white-gear" alt="Estilos CSS e/ou Bibliotecas JS" title="Estilos CSS e/ou Bibliotecas JS"></i>&nbsp;';
					} else { // Acesso WEB em geral
						$row['grafico'] = '<center><i class="cus-world" alt="Acesso WEB" title="Acesso WEB"></i>&nbsp;';
					}					
					//$row['grafico'] .= $row['attr']->data->method . '(' . $row['attr']->data->content_type . ') - <a href="http://' . $row['attr']->data->url .'" target="_blank">' . $row['attr']->data->url . '</a></center>';
					$row['grafico'] .= $row['attr']->data->method . ' - <a href="http://' . $row['attr']->data->url .'" target="_blank">' . $row['attr']->data->url . '</a></center>';
					
				} else if ($row['attr']->type == 'unknows') { // Conteúdo desconhecido
					
					if (preg_match("/WhatsApp/", $row['attr']->data->l7prot)) { // WhatsApp
						$row['grafico'] = '<center><img src="/simce/assets/img/whatsapp.png" alt="WhatsApp" title="WhatsApp">&nbsp;';
					} else if (preg_match("/Google/", $row['attr']->data->l7prot) || preg_match("/google/i", $row['attr']->data->dst)) { // Google
						$row['grafico'] = '<center><img src="/simce/assets/img/google.png" alt="Google" title="Google">&nbsp;';
					} else if (preg_match("/Facebook/", $row['attr']->data->l7prot) || preg_match("/(facebook|fbcdn|fbstatic)/i", $row['attr']->data->dst)) { // Facebook
						$row['grafico'] = '<center><img src="/simce/assets/img/facebook.png" alt="Facebook" title="Facebook">&nbsp;';
					} else if (preg_match("/Unknown/", $row['attr']->data->l7prot)) { // Desconhecido
						$row['grafico'] = '<center><i class="cus-help" alt="Protocolo Desconhecido" title="Protocolo Desconhecido"></i>&nbsp;';
					} else {
						$row['grafico'] = '<center><i class="cus-exclamation" alt="Protocolo Não Mapeado" title="Protocolo Não Mapeado"></i>&nbsp;(' . $row['attr']->data->l7prot . ') ';
					}
					
					if ($row['attr']->data->dst_port == 443)
						$row['grafico'] .= '<a href="https://' . $row['attr']->data->dst .'" target="_blank">' . $row['attr']->data->dst . '</a></center>';
					else
						$row['grafico'] .= '<a href="http://' . $row['attr']->data->dst .'" target="_blank">' . $row['attr']->data->dst . '</a></center>';
					
				} else if ($row['attr']->type == 'mms') { // MMS
					
					$row['grafico'] = '<center><i class="cus-email-attach" alt="MMS" title="MMS"></i>&nbsp;';
					$row['grafico'] .= "De: {$row['attr']->data->from_num} - Para: {$row['attr']->data->to_num} - Cc: {$row['attr']->data->cc_num} - Cco: {$row['attr']->data->bcc_num}</center>";
					
				} else if ($row['attr']->type == 'unkfiles' || $row['attr']->type == 'httpfiles') { // Arquivos HTTP
					
					$row['grafico'] = '<center><i class="cus-page-white-lightning" alt="Arquivos HTTP" title="Arquivos HTTP"></i>&nbsp;';
					$row['grafico'] .= '<a href="/simce/interceptacoes/fileDownload/?file=' . $row['attr']->data->file_path .'" target="_blank">' . ((!empty($row['attr']->data->url)) ? $row['attr']->data->url : 'Url desconhecida') . '</a></center>';
					
					// Tamanho em formato humano					
					$base = log((empty($row['attr']->data->fsize) ? $row['attr']->data->file_size : $row['attr']->data->fsize)) / log(1024);
					$suffixes = array('B', 'Kb', 'Mb', 'Gb', 'Tb');   
					$size = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];

					// Ajusta o valor
					if (empty($row['tamanho']))
						$row['tamanho'] = $size;
					
				} else {
					
					$row['grafico'] = print_r( $row['attr'], true );					
					
				}
				
				// Define o tipo
				$row['tipo_orig'] = $row['tipo'];
				$row['tipo'] = '<center><i class="cus-folder-brick" alt="Interceptação de Dados" title="Interceptação de Dados"></i>&nbsp;</center>';
				
				if ($row['attr']->type != 'unkfiles' && $row['attr']->type != 'httpfiles') {
					// Tamanho em formato humano
					$base = log($row['tamanho']) / log(1024);
					$suffixes = array('B', 'Kb', 'Mb', 'Gb', 'Tb');   
					$size = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];

					// Ajusta o valor
					$row['tamanho'] = $size;
				}
				
				// Cache
				$row['tamanho_cache'] = $row['tamanho'];
				
				
			}
			
			// Timeline
			if (!empty($row['timeline']) && $row['timeline'] == 1)
				$row['status'] .= '<i class="cus-timeline-marker" alt="Adicionado a Cronologia de Eventos" title="Adicionado a Cronologia de Eventos"></i>&nbsp;';
			$row['status'] .= '</center>';

			// Adiciona as ações do registro
			$row['acoes'] =  '<center>';
			$row['acoes'] .= '<i class="cus-find" alt="Exibir detalhes" title="Exibir detalhes" style="cursor: pointer;" onclick="SiMCE.functions.showDetails( ' . $row['id'] . ' )"></i>';
			$row['acoes'] .= '</center>';
                        
			// Armazena os detalhes em sessão para leitura posterior mais rápida
			$app['session']->set('SIMCE_INTERCEPTION_' . $row['id'], $row);
                        			
		}
		
		// Auditoria
		$app['audit']->log(
			'interception_list',
			array(
				'success' => true,
				'data'    => array (
					'filter'   => $filter,
					'operacao' => $app['request']->get('operacao'),
					'unidade'  => $app['user']->getUnidade()
				)
			)
		);
		
		// Retorna as dados
		if (!empty($filter['format']) && $filter['format'] == 'zip') {
			return $app->json( array(
				//'file'    => '/simce/cache/' . basename( $zipFile )
				'success'   => true,
				'total'     => $obj->total,
				'binds'     => base64_encode(json_encode($binds)),
				'statement' => base64_encode(json_encode($statement))
			));
		} else {
			return $app->json( array(
				'aaData'               => $obj->rows,
				'iTotalRecords'        => $obj->total,
				'iTotalDisplayRecords' => $obj->total,
				'sEcho'                => $app['request']->get('sEcho'),
				'binds'                => $binds,
				'statement'            => $statement
			));
		}
		
	}

	/**
	 * Exibe o painel online
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function online( \Silex\Application $app ) {
		
		$mode = $app['request']->get('mode');
		
		// Obtem as interceptações ativas por alvo desta operação
		$alocacoes = \SiMCE\Classes\Alocacoes::getByQuery(
			" operacoes_id = :operacao AND ( inicio <= DATE(NOW()) AND fim >= DATE(NOW()) ) ",
			array(
				"operacao" => $app['request']->get('operacao')
			),
			true
		);
		
		// Agrupa as alocações por alvo e recurso
		$alvos = array();
		foreach( $alocacoes as $aloc ) {
			
			// Verifica se o usuário tem permissão para o painel online
			
			if ( $app['user']->isOperator() && $app['user']->hasPermissionByTarget( $aloc->bean->alvos_id, 'ACTION_INTERCEPTION_ONLINE' ) === false )
				continue;
			
			// Monta o objeto do alvo
			$alvos[ $aloc->bean->alvos_id ]["id"] = $aloc->bean->alvos_id;
			$alvos[ $aloc->bean->alvos_id ]["nome"] = $aloc->bean->alvos->nome;
			$alvos[ $aloc->bean->alvos_id ]["alocacoes"][] = array(
				"id"            => $aloc->bean->id,
				"recurso_id"    => $aloc->bean->recursos_id,
				"recurso_nome"  => $aloc->bean->recursos->nome,
				"recurso_tipo"  => $aloc->bean->recursos->tipo,
				"recurso_status"=> $aloc->bean->status,
				"inicio"        => $aloc->get('inicio'),
				"fim"           => $aloc->get('fim'),
				"identificacao" => $aloc->bean->identificacao,
				"oficio"        => $aloc->bean->oficio
			);
			
		}
		
		// Auditoria
		/*$app['audit']->log(
			'interception_online',
			array(
				'success' => true,
				'data'    => array (
					'mode'     => $app['request']->get('mode'),
					'operacao' => $app['request']->get('operacao')
				)
			)
		);*/
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'content'   => $app['twig']->render(
					basename(__DIR__) . '/Views/online.html',
					array(
						"alvos" => $alvos,
						"mode"  => $mode
					)
				)
			)
		);
			
	}
	
	/**
	 * Exibe os detalhes do registro
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function details( \Silex\Application $app ) {
		
		// Carrega o registro
		$registro = \SiMCE\Classes\Registros::getByID( $app['request']->get('id'), true );
		
		// Obtem os segmentos
		$segmentos = \SiMCE\Classes\Segmentos::getByQuery(
			" registros_id = :id ORDER BY inicio ",
			array( "id" => $app['request']->get('id') )
		);
		// Popula o voiceid
		foreach( $segmentos as &$seg )
			$seg['voiceid'] = \SiMCE\Classes\VoiceID::getByID ($seg['voiceid_id']);
		
		// Obtem os contatos da operação
		$contatos = \SiMCE\Classes\Contatos::getByQuery(
			" operacoes_id = :operacao ORDER BY nome ",
			array( "operacao" => $registro->get("operacoes_id") )
		);
		// Ajusta os contatos
		foreach( $contatos as &$cont ) {
			if (!empty($cont['alvo'])) {
				$cont['foto'] = \SiMCE\Classes\Alvos::getByID( $cont['alvo'], true )->get('foto');
			}
		}
		
		// Atualiza o registro para visualizado
		if (empty($registro->bean->relato)) {
			$registro->set('estado', \SiMCE\Classes\Registros::STATUS_VIEWED);
		}/* else {
			$registro->set('estado', \SiMCE\Classes\Registros::STATUS_TRANSCRIBED);
		}*/
		$registro->save();
		
		// Auditoria
		$app['audit']->log( 'interception_details', array( 'success' => true, 'data' => $app['request']->get('id') ));
		
		// Resultado
		return $app['twig']->render(
			basename(__DIR__) . '/Views/detalhes.html',
			array(
				"registro"  => $registro,
				"cache"     => $app['session']->get('SIMCE_INTERCEPTION_' . $registro->get('id')),
				"segmentos" => $segmentos,
				"contatos"  => $contatos,
				"hash"      => @md5_file($registro->getFilePath())
			)
		);
			
	}
	
	/**
	 * Realiza a escuta de um registro ja armazenado
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function play( \Silex\Application $app ) {
		
		// Obtem os parametros informados
		$id    = $app['request']->get('id');
		$start = $app['request']->get('start');
		$end   = $app['request']->get('end');
		$grid  = $app['request']->get('grid');
		$full  = 0;
		
		// Carrega o registro e obtem o arquivo
		$reg = \SiMCE\Classes\Registros::getByID($id,true);
		$file = $reg->getFilePath();

		// Verifica se foi informado o trecho do áudio
		if (!empty($start) && !empty($end)) {
			
			// Início
			list ( $hours, $minutes, $seconds ) = explode(":", $start);
			$start = ((intval($hours)*3600) + (intval($minutes)*60) + (intval($seconds)))*1000;
			
			// Fim
			list ( $hours, $minutes, $seconds ) = explode(":", $end);
			$end = ((intval($hours)*3600) + (intval($minutes)*60) + (intval($seconds)))*1000;
			
		} else { // Por padrão, exibe todo o conteúdo
			
			$full = 1;
			
		}
		
		// Define o nome do arquivo chunk
		$chunk_file = $file;
		if (!$full)
			$chunk_file = dirname($file ) . '/' . $id . '-' . $start . '-' . $end . '.wav';
		
		// Verifica se o áudio solicitado já existe
		if (!file_exists( $chunk_file )) {

			$chunk = new \Audero\WavExtractor\AuderoWavExtractor($file);
			$chunk->saveChunk( $start, $end, $chunk_file);
			
		}

		// Redireciona o navegador para o áudio
		$location = preg_replace("/.*records\//","/simce/records/", $chunk_file);

		// Auditoria
		if (empty($grid))
			$app['audit']->log( 'interception_play', array( 'success' => true, 'data' => array( 'id' => $id, 'start' => $start, 'end' => $end )));
		else
			$app['audit']->log( 'interception_play_grid', array( 'success' => true, 'data' => array( 'id' => $id, 'start' => $start, 'end' => $end )));
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'content'   => $location
			)
		);
		
	}

	/**
	 * Salva as informações no banco de dados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function save( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data       = $app['request']->get('data');
				
		// Atualiza o registro
		$registro = \SiMCE\Classes\Registros::getByID($data['id'], true);
		$registro->set('observacoes', $data['obs']);
		$registro->set('relato', $data['relato']);
		$registro->set('classificacao', $data['classificacao']);
		$registro->set('timeline', $data['timeline']);
		$registro->save();
		
		// Adiciona os contatos e armazena as transcrições
		foreach( $data['seg'] as $seg_id => $seg ) {
			
			// Obtem o objeto do segmento
			$segmento = \SiMCE\Classes\Segmentos::getByID($seg_id,true);
			
			// Atualiza a transcricao
			$segmento->set('transcricao', $seg['transcricao']);
			$segmento->save();
			
			// Auditoria
			$app['audit']->log( 'interception_seg_save', array( 'success' => true, 'data' => \SiMCE\Classes\Segmentos::getByID($seg_id) ));

			// Verifica se é um contato novo
			$arr = explode("-", $seg['voiceid']);
			$contato = false;
			
			if ($seg['voiceid'] == 0)
				continue;
			
			if (count($arr) == 3) { // Contato novo
				
				// Verifica se já foi cadastrado
				$tmp = \SiMCE\Classes\Contatos::getByQuery(
					" nome = :nome AND operacoes_id = :operacao ",
					array(
						'nome'     => $arr[1],
						'operacao' => $registro->get('operacoes_id')
					),
					true
				);
				
				if (count($tmp)) {
					$contato = array_pop($tmp);
				} else {

					$contato = new \SiMCE\Classes\Contatos();
					$contato->set('nome', $arr[1]);
					$contato->set('genero', $arr[2]);
					$contato->set('alvo', 0);
					$contato->set('operacoes', \SiMCE\Classes\Operacoes::getByID( $registro->get('operacoes_id'), true )->bean);
					$contato->save();
					
					// Auditoria
					$app['audit']->log( 'interception_contact_new', array( 'success' => true, 'data' => \SiMCE\Classes\Contatos::getByID( $contato->get('id') )));
					
				}
				
			} else { // Contato já existe

				$contato = \SiMCE\Classes\Contatos::getByID($arr[0], true);
				
			}
			
			// Registra o contato ao voiceid
			$voiceid = \SiMCE\Classes\VoiceID::getByID( $segmento->get('voiceid_id'), true );
			$voiceid->set('contatos', $contato->bean );
			$voiceid->save();
			
			// Auditoria
			$app['audit']->log( 'interception_voiceid', array( 'success' => true, 'data' => array( 'voiceid' => \SiMCE\Classes\VoiceID::getByID( $segmento->get('voiceid_id') ), 'contato' => \SiMCE\Classes\Contatos::getByID( $contato->get('id') ))));
			
		}
		
		// Auditoria
		$app['audit']->log( 'interception_save', array( 'success' => true, 'data' => \SiMCE\Classes\Registros::getByID($data['id']) ));
		
		// Resultado
		return $app->json(
			array(
				'success'   => true
			)
		);

	}
	
	/**
	 * Realiza o download do audio e/ou segmento selecionado
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function download( \Silex\Application $app ) {
		
		// Obtem as informações de áudio
		$obj = json_decode( self::play($app)->getContent() );

		// Obtem os parametros informados
		$id    = $app['request']->get('id');
		
		// Carrega o registro e obtem o arquivo
		$reg = \SiMCE\Classes\Registros::getByID($id,true);
		
		// Auditoria
		$app['audit']->log( 'interception_download', array( 'success' => true, 'data' => $id ));
		
		// Verifica se o usuário tem permissão para fazer o download
		if ( $app['user']->isAdmin() || $app['user']->hasPermissionByTarget( $reg->get('alvos_id'), 'ACTION_INTERCEPTION_DOWNLOAD' ) ) {
			
			return new \Symfony\Component\HttpFoundation\Response(
				file_get_contents( SIMCE_DIR . "/../" . $obj->content ),
				200,
				array(
					"Content-Type"        => "audio/x-wav",
					"Content-Disposition" => "attachment; filename=A-" . $app['request']->get('id') . ".wav",
					"Content-Length"      => filesize(SIMCE_DIR . "/../" . $obj->content)
				)
			);
			
		} else {
			
			return "";
			
		}
		
	}
	
	/**
	 * Retorna a lista de alvos disponíveis para o usuário
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function targets( \Silex\Application $app ) {

		// Obtem os alvos que o usuário tem permissão de visualizar
		$targets = $app['user']->getTargetsByAction( $app['request']->get('operacao'), "ACTION_INTERCEPTION_" );
		
		// Obtem os IDs e nomes dos alvos
		$arr = \SiMCE\Classes\Alvos::getByQuery(
			($app['user']->isAdmin())
				? " operacoes_id = :operacao ORDER BY nome ASC "
				: " operacoes_id = :operacao AND id IN ( " . implode(",", $targets) . " ) ORDER BY nome ASC "
			,
			array(
				"operacao" => $app['request']->get('operacao')
			)
		);
		
		$ret = null;
		foreach( $arr as $a ) {
			$ret[] = array(
				"id"   => $a["id"],
				"nome" => $a["nome"]
			);
		}

		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $ret
			)
		);
		
	}
	
	/**
	 * Realiza a escuta online de um áudio
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function playOnline( \Silex\Application $app ) {
		
		// Obtem os parametros informados
		$id    = $app['request']->get('id');
		
		// Auditoria
		$app['audit']->log( 'interception_play_online', array( 'success' => true, 'data' => $id ));
		
		// Carrega a alocação pelo ID
		$aloc = \SiMCE\Classes\Alocacoes::getByID($id,true);
		
		// Obtem o recurso
		$recurso = $aloc->bean->recursos->link;
		
		// Encontra o arquivo referente à extensão
		foreach( new \DirectoryIterator( AST_SPOOL_DIR ) as $fileInfo ) {
			
			if (substr($fileInfo->getFilename(),0,1) == '.')
				continue;

			
			// Verifica se o arquivo é o desejado
			if (preg_match("/REC\-$recurso/", $fileInfo->getFilename())) {
				
				// Cria a função para o stream
				$stream = function() use( $fileInfo ) {
					
					// Abre o arquivo e define o modo de leitura
					$fh = fopen( $fileInfo->getPathname(), "r" );
					stream_set_blocking( $fh, 0 );
					//fread( $fh, 44 );

					// Seta o ponteiro para o final e aguarda 1 segundo
					fseek( $fh, 0, SEEK_END );
					$last = ftell( $fh );
					//print "Tamanho do arquivo $last<br>\n";
					//ob_flush(); flush();
					fseek( $fh, $last - ( 16000 * 2 ), SEEK_SET );
					//print " Aguarda 1 segundo <br>\n";
					//ob_flush(); flush();
					//sleep(1);
					
					// Retorna o cabeçalho inicial do mp3
					echo chr ( 0xff) . chr ( 0xe3) . chr ( 0x48) . chr ( 0xc4);
					ob_flush(); flush();
					
					// Faz o stream do conteúdo
					$delayCount = 0;
					while( ($chunk = fread( $fh, 16000 )) !== false ) {
						$mp3 = \SiMCE\Classes\AGI::convertStreamToMp3( $chunk );
						//print " Convertendo wav (" . strlen( $chunk ) . ") para mp3 (" . strlen($mp3) . ") <br>\n";
						echo $mp3;
						ob_flush(); flush();
						//sleep(1);
						
						// Verifica se existe delay no áudio
						if (strlen($chunk) == 0) {
							$delayCount++;
							usleep(100000);
						} else {
							$delayCount = 0;
						}
						
						// Se não existe mais nada a ser processado, encerra
						if ($delayCount == 50)
							break;
							
						//$cur = ftell( $fh );
						//print " Posicao atual: $cur <br>\n";
						//ob_flush(); flush();
					}
					
					// Encerra o arquivo
					fclose( $fh );
					exit;
					
				};
				
				// Cria o retorno de stream
				return $app->stream(
					$stream,
					200,
					array(
						'Content-Type'   => 'audio/mpeg',
						//'Connection'     => 'keep-alive',
						//'Content-Length' => 220000000,
						'Cache-Control'  => 'max-age=86400',
						'Pragma'         => 'no-cache'
					)
				);
				
			}

		}

		return '';

	}
	
	/**
	 * Retorna a lista de contatos disponíveis para o usuário
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function contacts( \Silex\Application $app ) {

		// Obtem os alvos que o usuário tem permissão de visualizar
		$targets = $app['user']->getTargetsByAction( $app['request']->get('operacao'), "ACTION_INTERCEPTION_" );
		
		// Obtem os IDs e nomes dos alvos
		$arr = \SiMCE\Classes\Contatos::getByQuery(
			($app['user']->isAdmin())
				? " operacoes_id = :operacao ORDER BY nome ASC "
				: " operacoes_id = :operacao AND id IN ( select contatos_id from voiceid where id in ( select voiceid_id from segmentos where registros_id in ( select id from registros where alvos_id IN ( " . implode(",", $targets) . " ) ))) ORDER BY nome ASC " 
			,
			array(
				"operacao" => $app['request']->get('operacao')
			)
		);
		
		$ret = null;
		foreach( $arr as $a ) {
			$ret[] = array(
				"id"   => $a["id"],
				"nome" => $a["nome"]
			);
		}

		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $ret
			)
		);
		
	}	

	/**
	 * Atualiza o status do registro
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function updateStatus( \Silex\Application $app ) {
            
		$id = $app['request']->get('id');
		$status = $app['request']->get('status');

		// Carrega o registro
		$reg = \SiMCE\Classes\Registros::getByID($id, true);
		$reg->set('classificacao', $status);
		$reg->set('estado', \SiMCE\Classes\Registros::STATUS_VIEWED);
		$reg->save();

		// Resultado
		return $app->json(
				array(
						'success' => true
				)
		);
            
	}
	
	/**
	 * Faz o download do arquivo http
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function fileDownload( \Silex\Application $app ) {
        
		$file = $app['request']->get('file');
		
		ob_start();
		system('/usr/bin/file -i -b ' . realpath($file));
		$type = ob_get_clean();
		$parts = explode(';', $type);
		$filetype=trim($parts[0]);
		
		header("Content-type: $filetype");
		readfile($file);
		exit(0);
            
	}

	/**
	 * Inicia o backup e informa o código para consulta do status
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function startExport( \Silex\Application $app ) {

		// Inicializa o processo de exportação em background
		$proc_id = uniqid("export", true);
		exec(SIMCE_DIR . "/scripts/simce-export.php 0 {$proc_id} {$app['request']->get("binds")} {$app['request']->get("statement")} >/dev/null 2>/dev/null &");

		// Resultado
		return $app['twig']->render(
			basename(__DIR__) . '/Views/export.html',
			array(
				"total"  => $app['request']->get('total'),
				"status" => $proc_id
			)
		);

	}

	/**
	 * Obtem o status da exportação
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function statusExport( \Silex\Application $app ) {

		$file_id = $app['request']->get('proc');
		$statusFile = SIMCE_CACHE_DIR . "status-" . $file_id . ".status";

		// Faz a leitura do arquivo e parse do conteúdo
		$stream = file_get_contents( $statusFile );
		$obj = json_decode( $stream );

		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $obj
			)
		);
		
	}

}

?>
