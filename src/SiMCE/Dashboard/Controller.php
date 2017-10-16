<?php

/**
 * Controlador responsável por manipular os Dashboards do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Dashboard;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory ( \Silex\Application $app, $prefix ) {
		$app->match( $prefix ,                         array(__CLASS__,'index')            );
		$app->match( $prefix . "active_interception/", array(__CLASS__,'activeInterception'));
		$app->match( $prefix . "interception_history/",array(__CLASS__,'interceptionHistory'));
		$app->match( $prefix . "system_performance/",  array(__CLASS__,'systemPerformance'));
		$app->match( $prefix . "list/" ,         array(__CLASS__,'getList')       );
	}
	
	/**
	 * Exibe o dashboard inicial
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function index ( \Silex\Application $app ) {
		
		$data = array();
		
		// Obtem as informações de sistema
		$data['cpu_model']   = \SiMCE\Classes\System::getCpuModel();
		$data['memory_size'] = \SiMCE\Classes\System::getMemSize();
		$data['disk_size']   = \SiMCE\Classes\System::getDiskSize();
		
		// Obtem as informações de fluxo e1, sinal gsm e internet
		$data['e1_status']  = \SiMCE\Classes\E1Stats::getCurrent();
		$data['gsm_status'] = \SiMCE\Classes\MobileStats::getCurrent();
		$data['int_status'] = \SiMCE\Classes\InternetStats::getCurrent();
	
		// Mostra as alocações ativas
		$arr = \SiMCE\Classes\Alocacoes::getByQuery(
			" :date BETWEEN inicio AND fim AND operacoes_id = :operacao ",
			array(
				'date'     => date('Y-m-d'),
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
				'desvio'        => (!empty($a->bean->desvio_para) ? (\SiMCE\Classes\Alvos::getByID($a->get('desvio_para'),true)->get('nome') . " - " . \SiMCE\Classes\Alvos::getByID($a->get('desvio_para'),true)->get('telefone')) : "-"),
				'desvio_via'    => $a->get('desvio_via')
			);

		}
		ksort( $rows );
		$data['rows'] = $rows;
	
		// Auditoria
		$app['audit']->log('dashboard_load', array( 'success' => true ));
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'error'     => false,
				'content'   => $app['twig']->render( basename(__DIR__) . '/Views/index.html', $data ),
				'onLoad'    => $app['twig']->render( basename(__DIR__) . '/Views/onLoad.js' ),
				'functions' => $app['twig']->render( basename(__DIR__) . '/Views/functions.js' ),
				'onUnload'  => false
			)
		);
			
	}
	
	/**
	 * Exibe o gráfico de interceptações ativas
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function activeInterception ( \Silex\Application $app ) {
		
		// Obtem os alvos que o usuário tem permissão de visualizar
		$targets = $app['user']->getTargetsByAction( $app['request']->get('operacao'), "ACTION_INTERCEPTION_" );
		
		//==================================
		//
		// Cria o corpo padrão da query
		//
		//==================================
		$statement = null;
		$binds = array();
		if ($app['user']->isAdmin()) {
			$statement         = " operacoes_id = :operacao AND status = :status AND ( inicio <= DATE(NOW()) AND fim >= DATE(NOW()) ) ";
			$binds["operacao"] = $app['request']->get('operacao');
			$binds["status"]   = "busy";
		} else {
			$statement         = " operacoes_id = :operacao AND alvos_id IN ( " . implode(",", $targets) . " ) AND status = :status AND ( inicio <= DATE(NOW()) AND fim >= DATE(NOW()) ) ";
			$binds["operacao"] = $app['request']->get('operacao');
			$binds["status"]   = "busy";
		}
		
		//===============================================
		//
		// Executa a query com os parametros informados
		//
		//===============================================
		$rows = \SiMCE\Classes\Alocacoes::getByQuery(
			$statement,
			$binds
		);

		// Auditoria
		//$app['audit']->log('dashboard_active_interceptions', array( 'success' => true, 'data' => $app['request']->get('operacao') ));
		
		// Resultado
		return $app->json(
			array(
				"success" => true,
				"content" => array(
					"time"  => date("H:i:s"),
					"value" => count($rows)
				)
			)
		);
		
	}
	
	/**
	 * Exibe o histórico de interceptações nas últimas 24 horas
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function interceptionHistory ( \Silex\Application $app ) {
		
		// Obtem os alvos que o usuário tem permissão de visualizar
		$targets = $app['user']->getTargetsByAction( $app['request']->get('operacao'), "ACTION_INTERCEPTION_" );
		
		//==================================
		//
		// Cria o corpo padrão da query
		//
		//==================================
		$statement = null;
		$binds = array();
		if ($app['user']->isAdmin()) {
			$statement         = " operacoes_id = :operacao AND tipo = :tipo AND data >= DATE_SUB(NOW(), INTERVAL 23 HOUR) ";
		} else {
			$statement         = " operacoes_id = :operacao AND alvos_id IN ( " . implode(",", $targets) . " ) AND tipo = :tipo AND data >= DATE_SUB(NOW(), INTERVAL 23 HOUR) ";
		}
		$binds["operacao"] = $app['request']->get('operacao');
		$binds["tipo"]     = $app['request']->get('tipo');
		
		//===============================================
		//
		// Executa a query com os parametros informados
		//
		//===============================================
		$rows = \R::getAll(
			" SELECT DATE_FORMAT(`data`,'%H:00') as `hour`,count(*) as `value` " . 
			" FROM registros " . 
			" WHERE $statement " . 
			" GROUP BY 1 ",
			$binds
		);
		
		// Cria o array de retorno
		$dataArray = array();
		$curTime = time() - 82800;
		for ( $i = 0; $i < 24; $i++ ) {
			$hour  = date("H:00", $curTime);
			$value = 0;
			
			// Verifica no array o indice
			foreach( $rows as $index => $o ) {
				if ($o['hour'] == $hour) {
					$value = $o['value'];
					unset( $rows[$index] );
				}
			}
			
			$dataArray[] = array(
				"hour"  => $hour,
				"value" => $value
			);
			
			$curTime += 3600;
		}

		// Auditoria
		$app['audit']->log(
			'dashboard_interception_history', 
			array(
				'success' => true,
				'data'    => array( 
					'operacao' => $app['request']->get('operacao'),
					'tipo'     => $app['request']->get('tipo')
				)
			)
		);
		
		// Resultado
		return $app->json(
			array(
				"success" => true,
				"content" => $dataArray
			)
		);
		
	}
	
	/**
	 * Exibe a performance do sistema
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function systemPerformance ( \Silex\Application $app ) {
		
		// Resultado
		return $app->json(
			array(
				"success" => true,
				"content" => \SiMCE\Classes\SystemStats::getCurrentPerformance()
			)
		);
		
	}
	
	/**
	 * Retorna a lista de usuários ativos
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getList(\Silex\Application $app ) {
		
		// Obtem a lista de beans de acordo com o tipo do usuário, com resultados paginados
		$obj = \SiMCE\Classes\Usuarios::getByPage(
			" unidades_id = :id AND online = :online ORDER BY nome ",
			array(
				"id"     => $app['user']->getUnidade(),
				"online" => 1
			),
			$app["request"]->get("iDisplayStart"),
			$app["request"]->get("iDisplayLength")
		);
		
		// Ajusta o valor do login
		foreach( $obj->rows as &$o )
			$o['last_login'] = \SiMCE\Classes\DataRecord::dbToDateTime( $o['last_login'] );
		
		
		// Retorna as dados
		return $app->json( array(
			'aaData'               => $obj->rows,
			'iTotalRecords'        => $obj->total,
			'iTotalDisplayRecords' => $obj->total,
			'sEcho'                => $app['request']->get('sEcho'),
		));
		
	}
	
}

?>
