<?php

/**
 * Controlador responsável por manipular a rede de relacionamentos
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Network;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		$app->match( $prefix ,            array(__CLASS__,'index')  );
		$app->match( $prefix . "view/",   array(__CLASS__,'view')   );
		$app->match( $prefix . "details/",array(__CLASS__,'details'));
		$app->match( $prefix . "targets/",    array(__CLASS__,'targets'));
		$app->match( $prefix . "contacts/",   array(__CLASS__,'contacts'));
		$app->match( $prefix . "graph/",   array(__CLASS__,'graph'));
	}
	
	/**
	 * Exibe a tela inicial
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function index( \Silex\Application $app ) {
		
		// Verifica se foi solicitado o modo full screen
		if ($app['request']->get('full') == 1) {
			
			return $app['twig']->render( basename(__DIR__) . '/Views/full.html',
				array(
					'content' => \SiMCE\Network\Controller::view( $app )->getContent(),
					'module'  => $app['twig']->render( basename(__DIR__) . '/Views/functions.js' )
				)
			);
			
		} else {
		
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
			
	}
	
	/**
	 * Monta o conteúdo da rede de relacionamento
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function view( \Silex\Application $app ) {
		
		// Cria a configuração da timeline
		$config = array(
			"success" => true,
			"content" => null
		);
		
		// Obtem os dados da operação
		$mode = $app['request']->get('mode');
		$filter = $app['request']->get('filter');
		$operation = \SiMCE\Classes\Operacoes::getByID( $app['request']->get('operacao'), true );
		
		//=============================
		//
		// Adiciona a operação
		//
		//=============================
		$config["content"] = array(
			"id"       => 0,
			"name"     => "<span class='cus-cog'></span> " . $operation->get('nome'),
			"children" => array()
		);
	
		//=============================
		//
		// Adiciona os alvos e os contatos
		//
		//=============================
		
		// Prepara o filtro do contato
		$filter_alvos = false;
		if (!empty($filter['alvos_id']))
			$filter_alvos = " AND id IN (" . implode(",", $filter['alvos_id']) . ")";
		$alvos = \SiMCE\Classes\Alvos::getByQuery(
			" operacoes_id = :operacao $filter_alvos ",
			array( "operacao" => $operation->get("id") ),
			true
		);
		foreach( $alvos as $alvo ) {
		
			// Cria o alvo
			$img = ($alvo->get("sexo") == "M") ? "<span class='cus-user'></span> " : "<span class='cus-user-female'></span> ";
			if ($alvo->get('foto')) {
				$img = "<img src='" . $alvo->get('foto') . "' width='32' height='32'><br>";
			}
			$target = array(
				"id"       => $alvo->get("id"),
				"name"     => "<center>" . $img . $alvo->get("nome") . "</center>",
				"data" => array( "type" => 0 ),
				"children" => array()
			);

			// Prepara os filtros de data e de interlocutores
			$filter_contatos = false;
			$filter_data = false;
			if (!empty($filter['contatos_id']))
				$filter_contatos = " AND contatos_id IN (" . implode(",", $filter['contatos_id']) . ")";
			if (!empty($filter['data'])) {
				if (!empty($filter['data']['start'])) {
					$arr = explode('/', $filter['data']['start']);
					$filter_data .= " AND DATE(data) >= '" . $arr[2] . '-' . $arr[1] . '-' . $arr[0] . "' ";
				}
				if (!empty($filter['data']['end'])) {
					$arr = explode('/', $filter['data']['end']);
					$filter_data .= " AND DATE(data) <= '" . $arr[2] . '-' . $arr[1] . '-' . $arr[0] . "' ";
				}
			}

			// Obtem a lista de contatos do alvo
			$rows = \R::getAll(
				" select * from voiceid where contatos_id IS NOT NULL $filter_contatos AND id in (select voiceid_id from segmentos where registros_id in (select id from registros where alvos_id = :alvo $filter_data))",
				array( "alvo" => $alvo->get("id") )
			);
			foreach( $rows as $row ) {
				
				// Contato
				if ($row['contatos_id'] !== null) {

					// Obtem os dados do contato
					$contato = \SiMCE\Classes\Contatos::getByID( $row['contatos_id'] );
					
					// Se o contato for um alvo
					if ( $contato['alvo'] != 0 ) {
						
						// Obtem os dados do alvo se for diferente do alvo atual
						$img = ($contato["sexo"] == "M") ? "<span class='cus-user'></span> " : "<span class='cus-user-female'></span> ";
						if ( $contato['alvo'] != $alvo->get("id") ) {
							$xAlvo = \SiMCE\Classes\Alvos::getByID( $contato['alvo'], true );
							if ($xAlvo->get('foto')) {
								$img = "<img src='" . $xAlvo->get('foto') . "' width='32' height='32'><br>";
							}
							$target["children"][] = array(
								"id"   => $xAlvo->get("id"),
								"name" => "<center>" . $img . $xAlvo->get("nome") . "</center>",
								"data" => array( "type" => 0 )
							);
						}
						
					} else {
						$img = ($contato["sexo"] == "M") ? "<span class='cus-user'></span> " : "<span class='cus-user-female'></span> ";
						if ($contato['foto']) {
							$img = "<img src='" . $contato['foto'] . "' width='32' height='32'><br>";
						}
						$target["children"][] = array(
							"id"   => $contato["id"] + 100000,
							"name" => "<center>" . $img . $contato["nome"] . "</center>",
							"data" => array( "type" => 1 )
						);
					}
					
				} else { // Desconhecido
					if ($mode == 0) {
						$target["children"][] = array(
							"id"   => $row["id"] + 200000,
							"name" => '<span class="cus-help"></span> Contato Desconhecido #' . $row["speaker"],
							"data" => array( "type" => 2 )
						);
					}
				}
				
				
				
			}
			
			// Adiciona o alvo
			$config["content"]["children"][] = $target;
			
		}
		
		// Auditoria
		$app['audit']->log( 'network_load', array( 'success' => true, 'data' => array( 'mode' => $mode, 'operacao' => $app['request']->get('operacao') )));
		
		//=============================
		//
		// Retorna a configuração
		//
		//=============================
		return $app->json( $config );
		
	}
	
	/**
	 * Exibe os detalhes do alvo/interlocutor selecionado
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function details( \Silex\Application $app ) {
		
		// Obtem o ID do contato/interlocutor
		$id    = $app['request']->get('id');
		$type  = $app['request']->get('type');
		$nodes = explode("|", $app['request']->get('nodes'));
		
		$data = null;
		$relations = null;
		
		//============================
		//
		// Alvos
		//
		//============================
		if ($type == 0) {
			
			$data = \SiMCE\Classes\Alvos::getByID ($id);
			$data['type'] = $type;
			
			// Obter os nodos
			foreach( $nodes as $node ) {

				if ( $node >= 200000 ) { // Se contato deconhecido
					$rows = \R::getRow(
						" select count(*) as count from registros where alvos_id = :alvo AND id in ( select registros_id from segmentos where voiceid_id = :voiceid ) ",
						array(
							"alvo"     => $id,
							"voiceid" => $node - 200000
						)
					);
					$relations[] = array(
						//"nome"  => \SiMCE\Classes\Contatos::getByID($node - 100000,true)->get("nome"),
						"nome"  => "Contato Desconhecido #" . \SiMCE\Classes\VoiceID::getByID($node - 200000,true)->get("speaker"),
						"count" => $rows["count"]
					);
				}  else if ( $node >= 100000 ) { // Se contato conhecido
					$rows = \R::getRow(
						" select count(*) as count from registros where alvos_id = :alvo AND id in ( select registros_id from segmentos where voiceid_id in ( select id from voiceid where contatos_id = :contato ) ) ",
						array(
							"alvo"    => $id,
							"contato" => $node - 100000
						)
					);
					$cont = \SiMCE\Classes\Contatos::getByID($node - 100000,true);
					$relations[] = array(
						"id"    => $cont->get("id"),
						"nome"  => $cont->get("nome"),
						"count" => $rows["count"]
					);
				}
				
			}

/**
 * 
 *	id	
32
nodes	
100039|55|56
type	
0
Source
id=32&type=0&nodes=100039|55|56
 */			
			
			
		//============================
		//
		// Interlocutores
		//
		//============================
		} else if ($type == 1) {
			
			$data = \SiMCE\Classes\Contatos::getByID ($id-100000);
			$data['type'] = $type;
			
			// Obter os nodos
			foreach( $nodes as $node ) {

				$rows = \R::getRow(
					" select count(*) as count from registros where alvos_id = :alvo AND id in ( select registros_id from segmentos where voiceid_id in ( select id from voiceid where contatos_id = :contato ) ) ",
					array(
						"alvo"    => $node,
						"contato" => $id - 100000
					)
				);
				$alv = \SiMCE\Classes\Alvos::getByID($node,true);
				$relations[] = array(
					"id"    => $alv->get("id"),
					"nome"  => $alv->get("nome"),
					"count" => $rows["count"]
				);
				
			}
			
		
		//============================
		//
		// Voice ID
		//
		//============================
		} else if ($type == 2) {
			
			$data = \SiMCE\Classes\VoiceID::getByID ($id-200000);
			$data["nome"] = "Contato Desconhecido #" . $data["speaker"];
			$data["sexo"] = "H";

			// Obter os nodos
			foreach( $nodes as $node ) {

				$rows = \R::getRow(
					" select count(*) as count from registros where alvos_id = :alvo AND id in ( select registros_id from segmentos where voiceid_id = :voiceid ) ",
					array(
						"alvo"    => $node,
						"voiceid" => $id - 200000
					)
				);
				$relations[] = array(
					"nome"  => \SiMCE\Classes\Alvos::getByID($node,true)->get("nome"),
					"count" => $rows["count"]
				);
				
			}
			
		}
		
		// Auditoria
		$app['audit']->log( 'network_details', array( 'success' => true, 'data' => array( 'id' => $id, 'obj' => $data )));
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'content'   => $app['twig']->render(
					basename(__DIR__) . '/Views/detalhes.html',
					array(
						'data'      => $data,
						'relations' => $relations
					)
				),
			)
		);
			
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
				: " operacoes_id = :operacao AND id IN (select contatos_id from voiceid where id in ( select voiceid_id from segmentos where registros_id in ( select id from registros where alvos_id IN ( " . implode(",", $targets) . " ) ))) ORDER BY nome ASC " 
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
	 * Mostra a imagem da rede de relacionamentos
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function graph( \Silex\Application $app ) {
		
		$chart = \SiMCE\Classes\Network::getGraph( $app['request']->get('operacao') );
		
		// Resultado
		return $app->json(
			array(
				'success' => true,
				'content' => $chart
			)
		);
		
	}
	
}

?>
