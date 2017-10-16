<?php

/**
 * Controlador responsável por manipular os relatórios do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Relatorios;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		
		// Opções globais
		$app->match( $prefix ,             array(__CLASS__,'index') );
		$app->match( $prefix . "menu/",    array(__CLASS__,'menu'));
		$app->match( $prefix . "filter/",  array(__CLASS__,'filter'));
		$app->match( $prefix . "loading/", array(__CLASS__,'loading'));
		$app->match( $prefix . "process/", array(__CLASS__,'process'));
		
		// Relatório de Operação
		$app->match( $prefix . "gen_operacao_report/",  array(__CLASS__,'genOperationReport'));
		
		// Relatório Judicial
		$app->match( $prefix . "show_record_form/",     array(__CLASS__,'showRecordForm'));
		$app->match( $prefix . "get_records/",          array(__CLASS__,'getRecords'));
		$app->match( $prefix . "show_target_form/",     array(__CLASS__,'showTargetForm'));
		$app->match( $prefix . "get_targets/",          array(__CLASS__,'getTargets'));
		$app->match( $prefix . "get_graph/",            array(__CLASS__,'getGraph'));
		$app->match( $prefix . "save_judicial_report/", array(__CLASS__,'saveJudicialReport'));
		$app->match( $prefix . "gen_judicial_report/",  array(__CLASS__,'genJudicialReport'));
		
		// Relatório de Auditoria
		$app->match( $prefix . "get_audits/",           array(__CLASS__,'getAudits'));

		// Relatório de Acesso
		$app->match( $prefix . "gen_access_report/",  array(__CLASS__,'genAccessReport'));

		// Relatório Interativo
		$app->match( $prefix . "gen_interactive_report/",  array(__CLASS__,'genInteractiveReport'));

		// Relatório de Canais
		$app->match( $prefix . "gen_channel_report/",  array(__CLASS__,'genChannelReport'));
		
		// Relatório de Desvios
		$app->match( $prefix . "gen_call_forward_report/",  array(__CLASS__,'genCallForwardReport'));
		
	}
	
	/**
	 * Exibe a tela inicial
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function index( \Silex\Application $app ) {

		// Auditoria
		$app['audit']->log( 'report_menu', array( 'success' => true, 'data' => $data ));
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'error'     => false,
				'content'   => $app['twig']->render( basename(__DIR__) . '/Views/index.html' ),
				'onLoad'    => false,
				'functions' => $app['twig']->render( basename(__DIR__) . '/Views/functions.js' ),
				'onUnload'  => false
			)
		);
			
	}
	
	/**
	 * Exibe o menu de relatórios
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function menu( \Silex\Application $app ) {
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'content'   => $app['twig']->render(
					basename(__DIR__) . '/Views/menu.html'
				)
			)
		);		
		
	}
	
	/**
	 * Exibe o filtro da tela
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function filter( \Silex\Application $app ) {
		
		// Obtem o tipo do relatorio
		$data = $app['request']->get('data');
		$reportType = $data['tipo'];
		
		// Adiciona o nome dos relatórios judicias salvos
		if ($reportType == 'judicial') {
			$data["relatorios"] = \SiMCE\Classes\RelatorioJudicial::getListByOperation( $app['request']->get('operacao') );
		}
		
		// Resultado
		$templateName = basename(__DIR__) . '/Views/' . $reportType . '_filter.html';
		$templateFile = __DIR__ . '/Views/' . $reportType . '_filter.html';
		return $app->json(
			array(
				'success'   => (file_exists($templateFile)) ? true : false,
				'error'     => (file_exists($templateFile)) ? false : "Não existem dados suficientes para gerar este relatório.",
				'content'   => (file_exists($templateFile)) ? $app['twig']->render($templateName, array( 'data' => $data )) : ''
			)
		);		
		
	}
	
	/**
	 * Exibe a tela de carregamento
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function loading( \Silex\Application $app ) {
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'content'   => $app['twig']->render(
					basename(__DIR__) . '/Views/loading.html'
				)
			)
		);		
		
	}
	
	/**
	 * Processa o relatório
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function process( \Silex\Application $app ) {
		
		// Obtem os parametros informados para o formulário
		$report = $app['request']->get('data');

		// Auditoria
		$app['audit']->log( 'report_process', array( 'success' => true, 'data' => $report ));
		
		if ($report['tipo'] == 'operacao') { // Relatório de operação
			return self::processOperationReport ( $app );
		} else if ($report['tipo'] == 'judicial') { // Relatório judicial
			return self::processJudicialReport( $app );
		} else if ($report['tipo'] == 'sistema') { // Relatório sistema
			return self::processSystemReport( $app );
		} else if ($report['tipo'] == 'auditoria') { // Relatório de auditoria
			return self::processAuditReport( $app );
		} else if ($report['tipo'] == 'interativo') { // Relatório interativo
			return self::processInteractiveReport( $app );
		} else if ($report['tipo'] == 'acesso') { // Relatório de acesso
			return self::processAccessReport( $app );
		} else if ($report['tipo'] == 'canais') { // Relatório de canais
			return self::processChannelReport( $app );
		} else if ($report['tipo'] == 'desvios') { // Relatório de canais
			return self::processCallForwardReport( $app );
		} else {
		
			// Resultado
			return $app->json(
				array(
					'success' => false,
					'error'   => 'Função indisponível no momento'
				)
			);				
			
		}
		
	}
	
	//=============================================
	//
	// Relatório de Operações
	//
	//=============================================
	
	/**
	 * Processa os dados do relatório de operação
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function processOperationReport( \Silex\Application $app ) {
	
		// Obtem os parametros
		$report   = $app['request']->get('data');
		$operacao = \SiMCE\Classes\Operacoes::getByID($app['request']->get('operacao'));
		$inicio = \SiMCE\Classes\DataRecord::dateToDb($report['inicio']);
		$fim = \SiMCE\Classes\DataRecord::dateToDb($report['fim']);

		// Obtem os alvos
		$alvos   = \SiMCE\Classes\Alvos::getByQuery( " operacoes_id = :operacao ", array( "operacao" => $operacao['id'] ) );

		// Define o total de registros da operação
		$opt = array(
			'operacao' => $operacao,
			'total' => \SiMCE\Classes\Registros::getCount(
				" operacoes_id = :operacao AND data BETWEEN :inicio AND :fim ",
				array(
					"operacao" => $operacao['id'],
					"inicio" => $inicio,
					"fim" => $fim
				)
			)
		);

		// Cria um array com as propriedades desejadas ( chave = query )
		$prop = array(
			'highPrio' => 'classificacao = ' . \SiMCE\Classes\Registros::PRIORITY_HIGH,
			'medPrio'  => 'classificacao = ' . \SiMCE\Classes\Registros::PRIORITY_MEDIUM,
			'lowPrio'  => 'classificacao = ' . \SiMCE\Classes\Registros::PRIORITY_LOW,
			'noPrio'   => 'classificacao = ' . \SiMCE\Classes\Registros::PRIORITY_NONE,
			'new'      => 'estado = ' . \SiMCE\Classes\Registros::STATUS_NEW,
			'view'     => 'estado = ' . \SiMCE\Classes\Registros::STATUS_VIEWED,
			'relato'   => 'LENGTH(relato) > 0',
			'obs'      => 'LENGTH(observacoes) > 0',
			'trans'    => 'id IN ( SELECT DISTINCT registros_id FROM segmentos WHERE LENGTH(transcricao) > 0 )'
		);

		// Percorre as propriedades obten os totais gerais da operação
		foreach( $prop as $key => $query ) {

			// Obtem o valor
			$opt[$key] = \SiMCE\Classes\Registros::getCount(
				" operacoes_id = :operacao AND $query AND data BETWEEN :inicio AND :fim ",
				array(
					"operacao" => $operacao['id'],
					"inicio" => $inicio,
					"fim" => $fim
				)
			);
			$opt[$key . "Perc"] = ($opt['total']) ? sprintf("%.3f", ( $opt[$key] * 100 ) / $opt['total'] ) : 0;

		}

		// Percorre os alvos para obter as estatisticas por alvo
		foreach( $alvos as $alvo ) {

			// Define o total de registros do alvo
			$alvo['total'] = \SiMCE\Classes\Registros::getCount(
				" operacoes_id = :operacao AND data BETWEEN :inicio AND :fim AND alvos_id = {$alvo['id']}",
				array(
					"operacao" => $operacao['id'],
					"inicio" => $inicio,
					"fim" => $fim
				)
			);

			// Percorre as propriedades obten os totais gerais da operação
			foreach( $prop as $key => $query ) {

				// Obtem o valor
				$alvo[$key] = \SiMCE\Classes\Registros::getCount(
					" operacoes_id = :operacao AND $query AND alvos_id = {$alvo['id']} AND data BETWEEN :inicio AND :fim ",
					array(
						"operacao" => $operacao['id'],
						"inicio" => $inicio,
						"fim" => $fim
					)
				);
				$alvo[$key . "Perc"] = ($alvo['total']) ? sprintf("%.3f", ( $alvo[$key] * 100 ) / $alvo['total'] ) : 0;

			}

			$opt['alvos'][] = $alvo;

		}

		// Calcula o tamanho
                exec("/usr/bin/du -sh " . SIMCE_DIR . "/records/" . $app['user']->getUnidade() . "/" . $operacao['id'], $output);
                list($size) = preg_split("/\s+/", $output[0] );
		$opt['size'] = $size;
	
		$opt['content'] = rawurlencode(serialize($opt));
		
		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/operacao_report.html',
					$opt
				)
			)
		);		
		
	}
	
	/**
	 * Gera o PDF do Relatório de Operação
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function genOperationReport( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data = $app['request']->get('data');
		$arr  = unserialize(rawurldecode($data['conteudo']));
		
		// Prepara o PDF
		$pdf = new \SiMCE\Classes\PDF( 'P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$y = array_slice( $arr['alvos'], 0, 2 );
		array_shift( $arr['alvos'] );
		array_shift( $arr['alvos'] );

		// Adiciona HTML
		$pdf->AddPage();
		$pdf->writeHTML(
			$app['twig']->render(
				basename(__DIR__) . '/Views/operacao_report_ope.html',
				array( 'raw' => $arr, 'operacao' => $arr['operacao'], 'alvos' => $y )
			),
			true, 0, true, 0
		);
		
		while( !empty($arr['alvos']) ) {
			$x = array_slice( $arr['alvos'], 0, 3 );
			array_shift( $arr['alvos'] );
			array_shift( $arr['alvos'] );
			array_shift( $arr['alvos'] );
			$pdf->AddPage();
			$pdf->writeHTML(
				$app['twig']->render(
					basename(__DIR__) . '/Views/operacao_report_target.html',
					array( 'alvos' => $x )
				),
				true, 0, true, 0
			);
		}	
	
		// Gera o PDF
		$pdf_file = '/SiMCE-OP-' . microtime(true) . '.pdf';
		$pdf->Output( SIMCE_CACHE_DIR . $pdf_file, 'F' );
		
		// Auditoria
		$app['audit']->log( 'report_operation_pdf', array( 'success' => true, 'data' => $data ));
		
		// Informa a mensagem para o usuário
		return $app->json( array(
			'success' => true,
			'file'    => '/simce/cache' . $pdf_file
		));
		
	}
	
	//=============================================
	//
	// Relatório Judicial
	//
	//=============================================
	
	/**
	 * Processa os dados do relatório judicial
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function processJudicialReport( \Silex\Application $app ) {
	
		// Parâmetros
		$data     = $app['request']->get('data');
		$operacao = $app['request']->get('operacao');
		
		// Carrega o relatório indicado caso tenha sido solicitado
		if ($data['modo'] == 2)
			$data['relatorio'] = \SiMCE\Classes\RelatorioJudicial::getByID( $data['id'] );
		
		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/judicial_report.html',
					array( 'data' => $data )
				)
			)
		);		
		
	}
	
	/**
	 * Exibe o formulário para adicionar registro
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function showRecordForm( \Silex\Application $app ) {

		// Obtem os alvos que o usuário tem permissão de visualizar
		$targets = $app['user']->getTargetsByAction( $app['request']->get('operacao'), "ACTION_INTERCEPTION_" );
		
		// Obtem os IDs e nomes dos alvos
		$alvos = \SiMCE\Classes\Alvos::getByQuery(
			($app['user']->isAdmin())
				? " operacoes_id = :operacao ORDER BY nome ASC "
				: " operacoes_id = :operacao AND id IN ( " . implode(",", $targets) . " ) ORDER BY nome ASC "
			,
			array(
				"operacao" => $app['request']->get('operacao')
			)
		);
		
		// Resultado
		return $app['twig']->render(
			basename(__DIR__) . '/Views/judicial_form_registro.html',
			array(
				"alvos" => $alvos
			)
		);
		
	}
	
	/**
	 * Obtem a tabela de registros selecionados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getRecords( \Silex\Application $app ) {

		// Obtem os dados via controller de interceptações
		$content = json_decode(\SiMCE\Interceptacoes\Controller::getList( $app )->getContent());

		// Obtem o filtro selecionados
		$filter = $app['request']->get('filter');
		
		// Verifica a quantidade de registros
		if ($content->iTotalRecords == 0) {
			return $app->json(
				array(
					'success' => false,
					'error'   => 'Nenhuma interceptação encontrada!'
				)
			);		
		} else {
			
			// Percorre os registros e adiciona os parâmetros da TCPDF
			foreach( $content->aaData as &$row ) {
				
				// Verifica se é áudio
				if ($row->tipo_orig == 'A') {
					
					// Obtem o caminho completo para o arquivo e gera os parametros

					$file_path = \SiMCE\Classes\Registros::getByID( $row->id, true )->getFilePath();
					$row->tcpdf_params = \TCPDF_STATIC::serializeTCPDFtagParameters(
						array(
							'','',
							4, 3,
							'Download do registro',
							array(
								'Subtype' => 'FileAttachment',
								'Name'    => 'Paperclip',
								'FS'      => $file_path
							)
						)
					);
					
					// Caso tenha sido solicitada a transcrição
					if (!empty($filter['transcricao'])) {
						
						// Obtem os segmentos
						$segmentos = \SiMCE\Classes\Segmentos::getByQuery(
							" registros_id = :id ORDER BY inicio ",
							array( "id" => $row->id )
						);
						
						// Popula o voiceid e o contato
						foreach( $segmentos as &$seg ) {
							$seg['voiceid'] = \SiMCE\Classes\VoiceID::getByID($seg['voiceid_id']);
							if (!empty($seg['voiceid']['contatos_id'])) {
								$seg['voiceid']['contatos_id'] = \SiMCE\Classes\Contatos::getByID($seg['voiceid']['contatos_id']);
							}
						}
						$row->segmentos = $segmentos;
						
					}
					
				}
				
			}
			
			return $app->json(
				array(
					'success' => true,
					'content' => $app['twig']->render(
						basename(__DIR__) . '/Views/judicial_form_registro_tabela.html',
						array(
							'rows'   => $content->aaData,
							'filter' => $filter
						)
					)
				)
			);
		}
		
	}

	/**
	 * Exibe o formulário para adicionar alvos
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function showTargetForm( \Silex\Application $app ) {

		// Obtem os alvos que o usuário tem permissão de visualizar
		$targets = $app['user']->getTargetsByAction( $app['request']->get('operacao'), "ACTION_REPORTS_JUDICIAL" );

		// Obtem os IDs e nomes dos alvos
		$alvos = \SiMCE\Classes\Alvos::getByQuery(
			($app['user']->isAdmin())
				? " operacoes_id = :operacao ORDER BY nome ASC "
				: " operacoes_id = :operacao AND id IN ( " . implode(",", $targets) . " ) ORDER BY nome ASC "
			,
			array(
				"operacao" => $app['request']->get('operacao')
			)
		);
		
		// Resultado
		return $app['twig']->render(
			basename(__DIR__) . '/Views/judicial_form_alvo.html',
			array(
				"alvos" => $alvos
			)
		);
		
	}
	
	/**
	 * Salva as informações no banco de dados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function saveJudicialReport( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data = $app['request']->get('data');

		// Cria um objeto para armazenar as informações
		try {
			
			// Armazena as informações do Alvo
			$obj = new \SiMCE\Classes\RelatorioJudicial();
			$obj->setAll( $data );
			$operacao = \SiMCE\Classes\Operacoes::getByID( $app['request']->get('operacao'), true );
			$obj->set( 'operacoes', $operacao->bean );
			$obj->save();
			
			// Auditoria
			$app['audit']->log( 'report_judicial_save', array( 'success' => true, 'data' => array( 'report' => $data, 'operacao' => $app['request']->get('operacao') )));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Por favor, preencha o nome e/ou conteúdo do relatório!",
				'fields'  => $obj->invalidFields,
				'msg'     => $e->getMessage()
			));
			
		}

	}
	
	/**
	 * Gera o PDF do Relatório
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function genJudicialReport( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data       = $app['request']->get('data');
		
		// Prepara o PDF
		$pdf = new \SiMCE\Classes\PDF( 'P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// Adiciona HTML
		$pdf->AddPage();
		$pdf->refWriteHTML( $data['conteudo'], true, 0, true, 0);
				
		// Gera o PDF
		$pdf_file = '/SiMCE-RJ-' . microtime(true) . '.pdf';
		$pdf->Output( SIMCE_CACHE_DIR . $pdf_file, 'F' );
		
		// Auditoria
		$data['conteudo'] = null;
		$app['audit']->log( 'report_judicial_pdf', array( 'success' => true, 'data' => $data ));
		
		// Informa a mensagem para o usuário
		return $app->json( array(
			'success' => true,
			'file'    => '/simce/cache' . $pdf_file
		));
		
	}
	
	/**
	 * Obtem a tabela de registros selecionados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getTargets( \Silex\Application $app ) {

		// Obtem os alvos
		$alvos = $app['request']->get('alvos');
		
		// Carrega cada um dos alvos selecionados
		$arrTargets = array();
		foreach( $alvos as $id ) {
			
			$alvo = \SiMCE\Classes\Alvos::getByID($id);
			$arrTargets[] = $alvo;
			
		}
		
		// Monta o retorno e devolve
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/judicial_form_alvo_tabela.html',
					array(
						'rows'   => $arrTargets
					)
				)
			)
		);

	}
	
	/**
	 * Obtem a tabela contendo a rede de relacionamentos
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getGraph( \Silex\Application $app ) {

		$chart = \SiMCE\Classes\Network::getGraph( $app['request']->get('operacao') );
				
		// Monta o retorno e devolve
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/judicial_form_rede_tabela.html',
					array(
						'chart'   => $chart
					)
				)
			)
		);

	}


	//=============================================
	//
	// Relatório de Acesso
	//
	//=============================================
	
	/**
	 * Processa os dados do relatório de acesso
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function processAccessReport( \Silex\Application $app ) {
	
		// Obtem os parametros
		$report   = $app['request']->get('data');
		$operacao = \SiMCE\Classes\Operacoes::getByID($app['request']->get('operacao'));
		$inicio = \SiMCE\Classes\DataRecord::dateToDb($report['inicio']);
		$fim = \SiMCE\Classes\DataRecord::dateToDb($report['fim']);
		$usuario = $report['usuario'];
		$ip = $report['ip'];
		
		// Obtem os registros de auditoria no período informado
		$arr = \SiMCE\Classes\Audit::getByQuery(
			" DATE(data) >= :start AND DATE(data) <= :end AND acao RLIKE 'user_(login|logout)' AND usuarios_id in ( select id from usuarios where unidades_id = :unidade ) ",
			array(
				'start'    => $inicio,
				'end'      => $fim,
				'unidade'  => $app["user"]->getUnidade()
			)
		);


		// Organiza os registros para agrupar login e logout
		$rows = array();
		foreach( $arr as $a ) {
			$o = json_decode($a['orig_param']);
			if ($o->success !== true)
				continue;

			// Usuario
			if (!empty($usuario)) {
				if (stristr($a['usuarios_id'],$usuario) === false)
					continue;
			}
			// IP
			if (!empty($ip)) {
				if (stristr($a['ip'],$ip) === false)
					continue;
			}

			// Adiciona o login
			if ($a['orig_acao'] == 'user_login') {
				$rows[] = (object) array(
					'user'   => $a['usuarios_id'],
					'ip'     => $a['ip'],
					'login'  => $a['data'],
					'logout' => '-'
				);
			}
			// Ajusta o logout
			if ($a['orig_acao'] == 'user_logout') {
				foreach( array_reverse($rows) as $x ) {
					if ($x->ip == $a['ip'] && $x->user == $a['usuarios_id']) {
						$x->logout = $a['data'];
						break;
					}
				}
			}
		}
				
		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/acesso_report.html',
					array(
						'rows' => $rows,
						'content' => rawurlencode(serialize($rows))
					)
				)
			)
		);		
		
	}

	/**
	 * Gera o PDF do Relatório de Acesso
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function genAccessReport( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data = $app['request']->get('data');
		$arr  = unserialize(rawurldecode($data['conteudo']));
		
		// Prepara o PDF
		$pdf = new \SiMCE\Classes\PDF( 'P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// Adiciona HTML
		$pdf->AddPage();
		$pdf->writeHTML(
			$app['twig']->render(
				basename(__DIR__) . '/Views/acesso_report_pdf.html',
				array( 'rows' => $arr )
			),
			true, 0, true, 0
		);
		
		// Gera o PDF
		$pdf_file = '/SiMCE-AC-' . microtime(true) . '.pdf';
		$pdf->Output( SIMCE_CACHE_DIR . $pdf_file, 'F' );
		
		// Auditoria
		$app['audit']->log( 'report_access_pdf', array( 'success' => true, 'data' => $data ));
		
		// Informa a mensagem para o usuário
		return $app->json( array(
			'success' => true,
			'file'    => '/simce/cache' . $pdf_file
		));
		
	}

	//=============================================
	//
	// Relatório de Canais
	//
	//=============================================
	
	/**
	 * Processa os dados do relatório de canais
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function processChannelReport( \Silex\Application $app ) {
	
		// Obtem os parametros
		$report   = $app['request']->get('data');
		$operacao = \SiMCE\Classes\Operacoes::getByID($app['request']->get('operacao'));
		$inicio = \SiMCE\Classes\DataRecord::dateToDb($report['inicio']);
		$fim = \SiMCE\Classes\DataRecord::dateToDb($report['fim']);
	
		// Obtem os registros de auditoria no período informado
		$arr = \SiMCE\Classes\Alocacoes::getByQuery(
			" DATE(inicio) BETWEEN :start AND :end AND operacoes_id = :operacao ",
			array(
				'start'    => $inicio,
				'end'      => $fim,
				'operacao' => $app['request']->get('operacao')
			),
			true
		);

		$rows = array();
		foreach( $arr as $a ) {
			$id = $a->bean->alvos->nome;
			if (empty($rows[ $id ])) {
				$cfg = array(
					'id'   => $a->bean->alvos->id,
					'nome' => $a->bean->alvos->nome
				);
				$tPerm = array();
				foreach( $a->bean->alvos->ownPermissoes as $p ) {
					//print $p->usuarios->nome . PHP_EOL;
					$tPerm[] = $p->usuarios->nome . " (" . $p->cargos->nome . ")";
				}
				$cfg['permissoes'] = (!empty($tPerm)) ?implode(",", $tPerm) : "-";
				$rows[ $id ] = $cfg;
			}
			$rows[$id]['alocacoes'][] = array(
				'inicio'        => $a->get('inicio'),
				'fim'           => $a->get('fim'),
				'vara'          => $a->get('oficio'),
				'recurso'       => $a->bean->recursos->nome,
				'identificacao' => $a->get('identificacao'),
				'desvio'        => (!empty($a->bean->desvio_para) ? (\SiMCE\Classes\Usuarios::getByID($a->get('desvio_para'),true)->get('nome') . " - " . \SiMCE\Classes\Usuarios::getByID($a->get('desvio_para'),true)->get('telefone')) : "-"),
				'desvio_via'    => $a->get('desvio_via')
			);

		}
		ksort( $rows );

		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/canais_report.html',
					array(
						'rows'    => $rows,
						'content' => rawurlencode(serialize($rows))
					)
				)
			)
		);		
		
	}

	/**
	 * Gera o PDF do Relatório de Canais
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function genChannelReport( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data = $app['request']->get('data');
		$arr  = unserialize(rawurldecode($data['conteudo']));
		
		// Prepara o PDF
		$pdf = new \SiMCE\Classes\PDF( 'P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// Adiciona HTML
		$pdf->AddPage();
		$pdf->writeHTML(
			$app['twig']->render(
				basename(__DIR__) . '/Views/canais_report_pdf.html',
				array( 'rows' => $arr )
			),
			true, 0, true, 0
		);
		
		// Gera o PDF
		$pdf_file = '/SiMCE-AC-' . microtime(true) . '.pdf';
		$pdf->Output( SIMCE_CACHE_DIR . $pdf_file, 'F' );
		
		// Auditoria
		$app['audit']->log( 'report_channel_pdf', array( 'success' => true, 'data' => $data ));
		
		// Informa a mensagem para o usuário
		return $app->json( array(
			'success' => true,
			'file'    => '/simce/cache' . $pdf_file
		));
		
	}

	//=============================================
	//
	// Relatório de Desvios
	//
	//=============================================
	
	/**
	 * Processa os dados do relatório de desvios
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function processCallForwardReport( \Silex\Application $app ) {
	
		// Obtem os parametros
		$report   = $app['request']->get('data');
		$operacao = \SiMCE\Classes\Operacoes::getByID($app['request']->get('operacao'));
		$inicio = \SiMCE\Classes\DataRecord::dateToDb($report['inicio']);
		$fim = \SiMCE\Classes\DataRecord::dateToDb($report['fim']);

		// Obtem os registros de auditoria no período informado
		$arr = \SiMCE\Classes\Registros::getByQuery(
			" (DATE(data) >= :start AND DATE(data) <= :end) AND operacoes_id = :operacao AND dial_status IS NOT null ",
			array(
				'start'    => $inicio,
				'end'      => $fim,
				'operacao' => $app['request']->get('operacao')
			),
			true
		);

		$rows = array();
		foreach( $arr as $a ) {

			// Obtem os responsáveis
			$tPerm = array();
			foreach( $a->bean->alvos->ownPermissoes as $p ) {
				$tPerm[] = $p->usuarios->nome . " (" . $p->cargos->nome . ")";
			}
			$responsaveis = implode(",", $tPerm);

			// Cria os registros
			$rows[] = array(
				'alvo'         => $a->bean->alvos->nome,
				'responsaveis' => $responsaveis,
				'num_desvio'   => preg_replace("/^[^\/]+\//", "", $a->get('dial_number')),
				'recurso'      => $a->bean->recursos->nome,
				'inicio'       => $a->get('data'),
				'fim'          => date('d/m/Y H:i:s',strtotime($a->bean->data) + $a->get('answer_time')),
				'duracao'      => gmdate('H:i:s', $a->get('answer_time')),
				'id'           => $a->get('id')
			);


		}

		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/desvios_report.html',
					array(
						'rows'    => $rows,
						'content' => rawurlencode(serialize($rows))
					)
				)
			)
		);		
		
	}

	/**
	 * Gera o PDF do Relatório de Desvios
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function genCallForwardReport( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data = $app['request']->get('data');
		$arr  = unserialize(rawurldecode($data['conteudo']));
		
		// Prepara o PDF
		$pdf = new \SiMCE\Classes\PDF( 'P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// Adiciona HTML
		$pdf->AddPage();
		$pdf->writeHTML(
			$app['twig']->render(
				basename(__DIR__) . '/Views/desvios_report_pdf.html',
				array( 'rows' => $arr )
			),
			true, 0, true, 0
		);
		
		// Gera o PDF
		$pdf_file = '/SiMCE-AC-' . microtime(true) . '.pdf';
		$pdf->Output( SIMCE_CACHE_DIR . $pdf_file, 'F' );
		
		// Auditoria
		$app['audit']->log( 'report_call_forward_pdf', array( 'success' => true, 'data' => $data ));
		
		// Informa a mensagem para o usuário
		return $app->json( array(
			'success' => true,
			'file'    => '/simce/cache' . $pdf_file
		));
		
	}

	//=============================================
	//
	// Relatório de Sistema
	//
	//=============================================
	
	/**
	 * Processa os dados do relatório de sistema
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function processSystemReport( \Silex\Application $app ) {
	
		// Obtem os parametros
		$report   = $app['request']->get('data');
		$operacao = \SiMCE\Classes\Operacoes::getByID($app['request']->get('operacao'));
		$inicio = \SiMCE\Classes\DataRecord::dateToDb($report['inicio']);
		$fim = \SiMCE\Classes\DataRecord::dateToDb($report['fim']);
		
		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/sistema_report.html',
					array(
						// Estatísticas de Sistema
						'system'   => \SiMCE\Classes\SystemStats::getAvail($inicio, $fim),
						// Feixe E1
						'links'    => \SiMCE\Classes\E1Stats::getAvail($inicio, $fim),
						// Internet
						'internet' => \SiMCE\Classes\InternetStats::getAvail($inicio, $fim),
						// GSM
						'gsm'      => \SiMCE\Classes\MobileStats::getAvail($inicio, $fim)
					)
				)
			)
		);		
		
	}

	//=============================================
	//
	// Relatório de Auditoria
	//
	//=============================================
	
	/**
	 * Processa os dados do relatório de auditoria
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function processAuditReport( \Silex\Application $app ) {
	
		// Obtem os parametros
		$report           = $app['request']->get('data');
		$operacao         = $app['request']->get('operacao');
		$report['inicio'] = \SiMCE\Classes\DataRecord::dateToDb($report['inicio']);
		$report['fim']    = \SiMCE\Classes\DataRecord::dateToDb($report['fim']);

		$vars[] = "operacao=" . rawurlencode($operacao);
		foreach($report as $k => $v)
			$vars[] = "$k=" . rawurlencode($v);

		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/auditoria_report.html',
					array(
						'params' => implode("&", $vars)
					)
				)
			)
		);		
		
	}
	
	/**
	 * Obtem a lista de auditoria de acordo com os parametros informados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getAudits( \Silex\Application $app ) {

		// Obtem os filtros especificados
		$usuario = $app["request"]->get("usuario");
		$ip      = $app["request"]->get("ip");
		$evento  = $app["request"]->get("evento");
		
		// Obtem a lista com resultados paginados
		$arr = \SiMCE\Classes\Audit::getByQuery(
			" data >= :inicio AND data <= :fim AND ( unidades_id = :unidade OR unidades_id IS NULL ) ORDER BY data DESC",
			array(
				'inicio'  => $app["request"]->get('inicio') . ' 00:00:00',
				'fim'     => $app["request"]->get('fim') . ' 23:59:59',
				'unidade' => $app["user"]->getUnidade()
			)
		);

		foreach($arr as $idx => &$v) {
			if (!empty($usuario)) {
				if (stristr($v['usuarios_id'],$usuario) === false) {
					unset($arr[$idx]);
					continue;
				}
			}
			if (!empty($ip)) {
				if (stristr($v['ip'],$ip) === false) {
					unset($arr[$idx]);
					continue;
				}
			}
			if (!empty($evento)) {
				if (stristr($v['acao'], $evento) === false) {
					unset($arr[$idx]);
					continue;
				}
			}
		}
	
		// Retorna as dados
		return $app->json( array(
			'aaData'               => array_slice( $arr, $app["request"]->get("iDisplayStart"), $app["request"]->get("iDisplayLength")),
			'iTotalRecords'        => count($arr),
			'iTotalDisplayRecords' => count($arr),
			'sEcho'                => $app['request']->get('sEcho'),
				"id"   => $app['user']->getUnidade(),
				"o_id" => $app['request']->get('operacao')
		));
		
	}

	//=============================================
	//
	// Relatório Interativo
	//
	//=============================================
	
	/**
	 * Processa os dados do relatório interativo
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function processInteractiveReport( \Silex\Application $app ) {

		/*
		// Especifica o arquivo zip que será criado caso seja solicitado
		$zipFile = SIMCE_CACHE_DIR . "registros-" . $app['request']->get('operacao') . "-" . microtime(true) . "-" . uniqid("", true) . ".zip";
		
		// Busca a lista de arquivos
		$arr = \SiMCE\Classes\Registros::getByQuery(
			" operacoes_id = :operacao AND DATE(data) >= :start AND DATE(data) <= :end ",
			array(
				'operacao' => $operacao,
				'start'    => $inicio,
				'end'      => $fim
			),
			true
		);
		
		// Percorre os registros e adiciona os áudios no backup
		foreach( $arr as $reg ) {
			if ($reg->get('tipo') == \SiMCE\Classes\Recursos::TYPE_AUDIO) {

				// Caso tenha sido definida uma senha
				$zOpts = false;
				if (!empty($report['password'])) {
					$zOpts = " -p'{$report['password']}' ";
				}
				
				// Converte para mp3 caso necessário
				if ($report['mp3'] == 1) {
					
					system("/usr/bin/ffmpeg -i " . $reg->getFilePath() . " -acodec libmp3lame /tmp/" . $reg->get('id') . ".mp3 >/dev/null 2>/dev/null");
					system("/usr/bin/7za a -tzip {$zOpts} -mem=AES256 {$zipFile} /tmp/" . $reg->get('id') . ".mp3 >/dev/null 2>/dev/null");
					
				} else {
					system("/usr/bin/7za a -tzip {$zOpts} -mem=AES256 {$zipFile} " . $reg->getFilePath() . " >/dev/null 2>/dev/null");
				}
				
				system("/usr/bin/7za a -tzip {$zOpts} -mem=AES256 {$zipFile} " . $reg->getImagePath( true ) . " >/dev/null 2>/dev/null");
				
			}
		}*/
	

		// Faz uma requisição interna para o controler de com os dados do relatório
		$content = json_decode(\SiMCE\Interceptacoes\Controller::getList( $app )->getContent());
	
		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/interativo_report.html',
					array(
						'content' => $content,
						'data'    => base64_encode(json_encode($app['request']->get('data'))),
						'proc'    => uniqid("report", true)
					)
				)
			)
		);
		
	}

	/**
	 * Iniia o processo de geração do relatório
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function genInteractiveReport( \Silex\Application $app ) {

		// Inicializa o processo de exportação em background
		exec(SIMCE_DIR . "/scripts/simce-export.php 1 {$app['request']->get("proc")} {$app['request']->get("binds")} {$app['request']->get("statement")} {$app['request']->get("opts")} >/dev/null 2>/dev/null &");
		$app['debug']->log( "ui::" . $app['user']->get('login'), SIMCE_DIR . "/scripts/simce-export.php 1 {$app['request']->get("proc")} {$app['request']->get("binds")} {$app['request']->get("statement")} {$app['request']->get("opts")} >/dev/null 2>/dev/null &");

		// Resultado
		return $app->json(
			array(
				'success' => true,
			)
		);

	}

}

?>
