<?php

/**
 * Controlador responsável por manipular os alvos e suas alocações
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Alvos;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		$app->match( $prefix ,                   array(__CLASS__,'index')         );
		$app->match( $prefix . "form/" ,         array(__CLASS__,'form')          );
		$app->match( $prefix . "save/" ,         array(__CLASS__,'save')          );
		$app->match( $prefix . "remove/",        array(__CLASS__,'remove')        );
		$app->match( $prefix . "list/" ,         array(__CLASS__,'getList')       );
		$app->match( $prefix . "loadAlocation/", array(__CLASS__,'loadAlocation') );
		$app->match( $prefix . "checkAlocation/",array(__CLASS__,'checkAlocation') );
		$app->match( $prefix . "loadPermission/",array(__CLASS__,'loadPermission') );
		$app->match( $prefix . "verifyCPF/",     array(__CLASS__,'verifyCPF') );
	}
	
	/**
	 * Lista os registros cadastrados
	 * e permite inserir, editar e remover
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
	 * Retorna o form de cadastro
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function form( \Silex\Application $app ) {
		
		// Verifica se foi informado um ID
		$data = null;
		$alocacoes = null;
		$permissoes = null;
		$id = $app['request']->get("id");
		$mode = $app['request']->get("mode");
		if (!empty($id)) {
			// Carrega o alvo
			$data = \SiMCE\Classes\Alvos::getByID( $id );
			// Carrega as alocações
			$alocacoes = \SiMCE\Classes\Alvos::getByID( $id, true )->get('ownAlocacoes');
			foreach( $alocacoes as $t )
				$t->recursos->nome;
			// Carrega as permissoes
			$tPerm = \SiMCE\Classes\Alvos::getByID( $id, true )->get('ownPermissoes');
			foreach( $tPerm as $t ) {
				$permissoes[] = array(
					"id"     => $t->usuarios_id,
					"nome"   => $t->usuarios->nome,
					"cargo"  => $t->cargos_id
				);
			}
			if ($mode == 1) {
				$alocacoes = array();
				$permissoes = array();	
			}
		}

		// Obtem a lista de recursos
		$recursos = \SiMCE\Classes\Recursos::getByQuery(
			" unidades_id = :id ",
			array( "id" => $app['user']->getUnidade() )
		);

		// Obtem a lista de usuários
		$usuarios = \SiMCE\Classes\Usuarios::getByQuery(
			" unidades_id = :id AND tipo = :tipo ORDER BY `nome`",
			array(
				"id"   => $app['user']->getUnidade(),
				"tipo" => "O"
			)
		);
		
		// Carrega os cargos cadastrados
		$cargos = \SiMCE\Classes\Cargos::getByQuery(
			" unidades_id = :id ",
			array( "id" => $app['user']->getUnidade() )
		);
	
		// Auditoria
		$app['audit']->log('target_form', array( 'success' => true, 'data' => $data ));
		if ($mode == 1)
			unset($data['id']);
		
		return $app['twig']->render(
			basename(__DIR__) . '/Views/form.html',
			array(
				'data'       => $data,
				'recursos'   => $recursos,
				'alocacoes'  => $alocacoes,
				'usuarios'   => $usuarios,
				'permissoes' => $permissoes,
				'cargos'     => $cargos
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
		
		// Obtem a lista de beans de acordo com o tipo do usuário, com resultados paginados
		$obj = \SiMCE\Classes\Alvos::getByPage(
			" unidades_id = :id AND operacoes_id = :o_id ORDER BY nome ",
			array(
				"id"   => $app['user']->getUnidade(),
				"o_id" => $app['request']->get('operacao')
			),
			$app["request"]->get("iDisplayStart"),
			$app["request"]->get("iDisplayLength")
		);
		
		// Auditoria
		$app['audit']->log('target_list', array( 'success' => true, 'data' => $app['request']->get('operacao') ));
		
		// Retorna as dados
		return $app->json( array(
			'aaData'               => $obj->rows,
			'iTotalRecords'        => $obj->total,
			'iTotalDisplayRecords' => $obj->total,
			'sEcho'                => $app['request']->get('sEcho'),
			'id'                   => $app['user']->getUnidade(),
			'o_id'                 => $app['request']->get('operacao')
		));
		
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
		$alocacoes  = $app['request']->get('alocacoes');
		$permissoes = $app['request']->get('permissoes');
		
		// Cria um objeto para armazenar as informações
		try {
			
			// Armazena as informações do Alvo
			$obj = new \SiMCE\Classes\Alvos();
			$obj->setAll( $data );
			
			// Ajusta o arquivo e obtem o conteúdo
			if ( !empty($data['foto'])) {
				$obj->set('foto', $app['session']->get( $data['foto'] ));
				$app['session']->set( $data['foto'], NULL );
			}
			
			$unidade = \SiMCE\Classes\Unidades::getByID( $app['user']->getUnidade(), true );
			$operacao = \SiMCE\Classes\Operacoes::getByID( $app['request']->get('operacao'), true );
			
			$obj->set( 'unidades',  $unidade->bean );
			$obj->set( 'operacoes', $operacao->bean );			
			$obj->save();
			
			// Auditoria
			$app['audit']->log('target_save', array( 'success' => true, 'data' => $data ));
			
			// Cria ou atualiza o contato baseado no Alvo
			$arrContato = \SiMCE\Classes\Contatos::getByQuery(
				" operacoes_id = :operacao AND alvo = :alvo ",
				array(
					"operacao" => $operacao->bean->id,
					"alvo"     => $obj->bean->id
				),
				true
			);
			if (!count($arrContato)) {
				$xObj = new \SiMCE\Classes\Contatos();
				$xObj->set( 'nome', 'Alvo - ' . $obj->get('nome') );
				$xObj->set( 'operacoes', $operacao->bean );
				$xObj->set( 'alvo', $obj->bean->id );
				$xObj->set( 'sexo', $obj->get('sexo') );
				$xObj->set( 'rg', '0000000000' );
				$xObj->set( 'cpf', '00000000000' );
				$xObj->save();
			} else {
				$xObj = array_pop($arrContato);
				$xObj->set( 'nome', 'Alvo - ' . $obj->get('nome') );
				$xObj->set( 'sexo', $obj->get('sexo') );
				$xObj->save();
			}

			//Armazena as informações das Alocações
			if ($alocacoes !== null) {
	
				// Obtem a lista de alocaçõe e atuais e remove aquelas desnecessárias
				$aIds = array();
				$aTmp = \SiMCE\Classes\Alocacoes::getByQuery(
					" alocacoes.alvos_id = :alvo ",
					array( "alvo" => $obj->bean->id )
				);
				foreach($aTmp as $xObj)
					$aIds[$xObj["id"]] = $xObj["id"];
				
				// Verifica o que foi submetido
				foreach( $alocacoes as $arrAloc ) {
					
					$aloc = new \SiMCE\Classes\Alocacoes();
					
					// Se for novo remove o ID
					unset($arrAloc["acao"]);
					if ($arrAloc["id"] == 0) {
						unset($arrAloc["id"]);
					} else {
						unset($aIds[$arrAloc["id"]]);
					}

					// Encontra o recurso
					$aRec = \SiMCE\Classes\Recursos::getByQuery(
						" nome = :nome ",
						array( "nome" => $arrAloc["recurso"] ),
						true
					);
					if (count($aRec) == 0) {
						
						// Informa a mensagem para o usuário
						return $app->json( array(
							'success' => false,
							'error'   => "Não foi possível encontrar o recurso " . $arrAloc["recurso"],
							'fields'  => $obj->invalidFields
						));
						
					} else {
						unset($arrAloc["recurso"]);
					}
					
					$aloc->setAll( $arrAloc );
					$aloc->set( 'operacoes', $operacao->bean );
					$aloc->set( 'alvos',     $obj->bean );
					$aloc->set( 'recursos',  $aRec[0]->bean );
					$aloc->save();

					// Auditoria
					$app['audit']->log('target_aloc_save', array( 'success' => true, 'data' => \SiMCE\Classes\Alocacoes::getByID( $aloc->get('id') )));
					
				}
				
				// Remove as alocações que sobraram
				foreach($aIds as $xId => $xTmp) {
					// Auditoria
					$app['audit']->log('target_aloc_remove', array( 'success' => true, 'data' => \SiMCE\Classes\Alocacoes::getByID($xId) ));
					\SiMCE\Classes\Alocacoes::remove ($xId);
				}
				
			} else { // Remove todas
				
				// Obtem a lista de alocaçõe e atuais e remove aquelas desnecessárias
				$aIds = array();
				$aTmp = \SiMCE\Classes\Alocacoes::getByQuery(
					" alocacoes.alvos_id = :alvo ",
					array( "alvo" => $obj->bean->id )
				);
				foreach($aTmp as $xObj) {
					// Auditoria
					$app['audit']->log('target_aloc_remove', array( 'success' => true, 'data' => \SiMCE\Classes\Alocacoes::getByID($xObj["id"]) ));
					\SiMCE\Classes\Alocacoes::remove($xObj["id"]);
				}
				
			}
			
			//Armazena as informações das Permissões
			if ($permissoes !== null) {
				
				// Remove as permissões
				$aTmp = \SiMCE\Classes\Permissoes::getByQuery(
					" alvos_id = :alvo ",
					array( "alvo" => $obj->bean->id )
				);
				foreach( $aTmp as $xObj )
					\SiMCE\Classes\Permissoes::remove($xObj["id"]);
				
				foreach($permissoes as $idx => $arrPerm) {
					
					$perm = new \SiMCE\Classes\Permissoes();
					$perm->set( 'alvos',    $obj->bean );
					$perm->set( 'usuarios', \SiMCE\Classes\Usuarios::getByID( $arrPerm["usuario"], true )->bean );
					$perm->set( 'cargos',   \SiMCE\Classes\Cargos::getByID( $arrPerm["cargo"], true )->bean );
					$perm->save();
					
					// Auditoria
					$app['audit']->log('target_permission_save', array( 'success' => true, 'data' => \SiMCE\Classes\Permissoes::getByID( $perm->get('id') )));
					
				}
				
			} else {
				
				// Remove as permissões
				$aTmp = \SiMCE\Classes\Permissoes::getByQuery(
					" alvos_id = :alvo ",
					array( "alvo" => $obj->bean->id )
				);
				foreach( $aTmp as $xObj ) {
					// Auditoria
					$app['audit']->log('target_permission_remove', array( 'success' => true, 'data' => \SiMCE\Classes\Permissoes::getByID( $xObj["id"] )));
					\SiMCE\Classes\Permissoes::remove($xObj["id"]);
				}
				
			}
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log('target_save', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Por favor, verifique o preenchimento dos campos marcados!",
				'fields'  => $obj->invalidFields,
				'msg'     => $e->getMessage()
			));
			
		}
		
	}
	
	/**
	 * Remove o usuário selecionado
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function remove( \Silex\Application $app ) {
		
		// Obtem o id informado
		$id = $app['request']->get('id');
		
		// Faz a remoção do registro informado
		try {
			
			// Auditoria
			$app['audit']->log('target_remove', array( 'success' => true, 'data' => \SiMCE\Classes\Alvos::getByID($id) ));
			
			\SiMCE\Classes\Alvos::remove($id);
						
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch( \Exception $e ) {
			
			// Auditoria
			$app['audit']->log('target_remove', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Ocorreu um erro ao remover o registro selecionado!"
			));
			
		}
		
		
	}
	
	/**
	 * Carrega o formulário de alocação
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function loadAlocation( \Silex\Application $app ) {

		$id = $app['request']->get('id');
		$index = $app['request']->get('index');
		$data = null;
		
		// Verifica se foi informada o ID da alocação
		if (!empty($id)) {
			$data = \SiMCE\Classes\Alocacoes::getByID($id);
		}
		
		// Carrega a operação para verificar a data final
		$operacao = \SiMCE\Classes\Operacoes::getByID( $app['request']->get('operacao') );
		
		// Obtem os recursos de celular disponíveis nesta unidade
		$recursos = \SiMCE\Classes\Recursos::getByQuery(
			" unidades_id = :unidade AND tipo = :tipo AND nome like 'SAC-%' ",
			array(
				"unidade" => $app['user']->getUnidade(),
				"tipo"    => "A"
			)
		);
		
		// Obtem os usuários da unidade
		$usuarios = \SiMCE\Classes\Usuarios::getByQuery(
			" unidades_id = :unidade ORDER BY `nome` ",
			array( "unidade" => $app['user']->getUnidade() )
		);
		
		// Auditoria
		$app['audit']->log('target_aloc_form', array( 'success' => true, 'data' => $data ));
		
		return $app->json( array(
			'success' => true,
			'content' => $app['twig']->render(
				basename(__DIR__) . '/Views/alocacao.html',
				array(
					"data"     => $data,
					"index"    => $index,
					"max_date" => $operacao["orig_fim"],
					"recursos" => $recursos,
					"usuarios" => $usuarios
				)
			)
		));
		
	}

	/**
	 * Verifica se é possível alocar o canal no período informado
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function checkAlocation( \Silex\Application $app ) {

		$id = $app['request']->get('id');
		$operacao = $app['request']->get('operacao');
		$recurso  = $app['request']->get('recurso');
		$ident  = $app['request']->get('ident');
		list( $day, $month, $year ) = explode("/", $app['request']->get('inicio'));
		$inicio = "$year-$month-$day";
		list( $day, $month, $year ) = explode("/", $app['request']->get('fim'));
		$fim = "$year-$month-$day";
		
		// Obtem o objeto do recurso
		$oRecurso = \SiMCE\Classes\Recursos::getByID($recurso,true);
		
		// Verifica se já existe alocação no período informado
		$arr = array();
		if (!empty($id)) {
			if ($oRecurso->get('tipo') == \SiMCE\Classes\Recursos::TYPE_AUDIO) {
				$arr = \SiMCE\Classes\Alocacoes::getByQuery(
					" alocacoes.id != :id AND alocacoes.recursos_id = :recurso AND ( ( :inicio BETWEEN alocacoes.inicio AND alocacoes.fim ) OR ( :fim BETWEEN alocacoes.inicio AND alocacoes.fim ) ) ",
					array(
						"id"      => $id,
						"recurso" => $recurso,
						"inicio"  => $inicio,
						"fim"     => $fim
					),
					true
				);
			} else if ($oRecurso->get('tipo') == \SiMCE\Classes\Recursos::TYPE_GSM) {
				$arr = \SiMCE\Classes\Alocacoes::getByQuery(
					" alocacoes.id != :id AND alocacoes.identificacao = :ident AND alocacoes.recursos_id = :recurso AND ( ( :inicio BETWEEN alocacoes.inicio AND alocacoes.fim ) OR ( :fim BETWEEN alocacoes.inicio AND alocacoes.fim ) ) ",
					array(
						"id"      => $id,
						"ident"   => $ident,
						"recurso" => $recurso,
						"inicio"  => $inicio,
						"fim"     => $fim
					),
					true
				);
			}
		} else {
			if ($oRecurso->get('tipo') == \SiMCE\Classes\Recursos::TYPE_AUDIO) {
				$arr = \SiMCE\Classes\Alocacoes::getByQuery(
					" alocacoes.recursos_id = :recurso AND ( ( :inicio BETWEEN alocacoes.inicio AND alocacoes.fim ) OR ( :fim BETWEEN alocacoes.inicio AND alocacoes.fim ) ) ",
					array(
						"recurso" => $recurso,
						"inicio"  => $inicio,
						"fim"     => $fim
					),
					true
				);
			} else if ($oRecurso->get('tipo') == \SiMCE\Classes\Recursos::TYPE_GSM) {
				$arr = \SiMCE\Classes\Alocacoes::getByQuery(
					" alocacoes.identificacao = :ident AND alocacoes.recursos_id = :recurso AND ( ( :inicio BETWEEN alocacoes.inicio AND alocacoes.fim ) OR ( :fim BETWEEN alocacoes.inicio AND alocacoes.fim ) ) ",
					array(
						"ident"   => $ident,
						"recurso" => $recurso,
						"inicio"  => $inicio,
						"fim"     => $fim
					),
					true
				);
			}
		}

		if (count($arr)) {
			return $app->json( array(
				'success' => false,
				'error'   => "Este recurso já está alocado neste período para o alvo <b>" . $arr[0]->bean->alvos->nome . "</b>, da operação <b>" . $arr[0]->bean->operacoes->nome . "</b>!"
			));
		} else {
			return $app->json( array(
				'success' => true
			));
		}
		
	}

	/**
	 * Carrega o formulário de perimssão
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function loadPermission( \Silex\Application $app ) {

		$id    = $app['request']->get('id');
		$nome = $app['request']->get('nome');

		// Carrega os cargos cadastrados
		$cargos = \SiMCE\Classes\Cargos::getByQuery(
			" unidades_id = :id ",
			array( "id" => $app['user']->getUnidade() )
		);
		
		return $app->json( array(
			'success' => true,
			'content' => $app['twig']->render(
				basename(__DIR__) . '/Views/permissao.html',
				array(
					"obj"    => array (
						"id"   => $id,
						"nome" => $nome
					),
					"cargos" => $cargos
				)
			)
		));
		
	}

	/**
	 * Verifica se existe outro alvo com este CPF
	 *
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function verifyCPF( \Silex\Application $app ) {

		$id  = $app['request']->get('id');
		$cpf = $app['request']->get('cpf');
		$cpf = preg_replace("/[^\d]+/", "", $cpf);

		// Busca os contatos
		$arr = array();
		if (empty($id))
			$arr = \SiMCE\Classes\Alvos::getByQuery(" cpf = :cpf ", array( "cpf" => $cpf ));
		else
			$arr = \SiMCE\Classes\Alvos::getByQuery(" cpf = :cpf AND id != :id ", array( "cpf" => $cpf, "id" => $id ));

		return $app->json( array(
			'success' => true,
			'content' => count($arr) ? array_shift($arr) : false
		));

	}
	
}

?>
