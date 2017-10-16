<?php

/**
 * Controlador responsável por manipular os contatos da operação
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Contatos;

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
		$id = $app['request']->get("id");
		$mode = $app['request']->get("mode");
		if (!empty($id)) {
			// Carrega o alvo
			$data = \SiMCE\Classes\Contatos::getByID( $id );
		}

		// Auditoria
		$app['audit']->log('contact_form', array( 'success' => true, 'data' => $data ));
		if ($mode == 1)
			unset($data['id']);
		
		return $app['twig']->render(
			basename(__DIR__) . '/Views/form.html',
			array(
				'data'       => $data
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
		
		// Obtem a lista com resultados paginados
		$obj = \SiMCE\Classes\Contatos::getByPage(
			" operacoes_id = :id AND alvo = 0 ORDER BY nome ",
			array(
				"id" => $app['request']->get('operacao')
			),
			$app["request"]->get("iDisplayStart"),
			$app["request"]->get("iDisplayLength")
		);
		
		// Auditoria
		$app['audit']->log('contact_list', array( 'success' => true, 'data' => $app['request']->get('operacao') ));
		
		// Retorna as dados
		return $app->json( array(
			'aaData'               => $obj->rows,
			'iTotalRecords'        => $obj->total,
			'iTotalDisplayRecords' => $obj->total,
			'sEcho'                => $app['request']->get('sEcho'),
				"id"   => $app['user']->getUnidade(),
				"o_id" => $app['request']->get('operacao')
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

		// Cria um objeto para armazenar as informações
		try {
			
			// Armazena as informações do Alvo
			$obj = new \SiMCE\Classes\Contatos();
			$obj->setAll( $data );
			
			// Ajusta o arquivo e obtem o conteúdo
			if ( !empty($data['foto'])) {
				$obj->set('foto', $app['session']->get( $data['foto'] ));
				$app['session']->set( $data['foto'], NULL );
			}
			
			$operacao = \SiMCE\Classes\Operacoes::getByID( $app['request']->get('operacao'), true );
			$obj->set( 'operacoes', $operacao->bean );
			$obj->save();
			
			// Auditoria
			$app['audit']->log('contact_save', array( 'success' => true, 'data' => \SiMCE\Classes\Contatos::getByID( $obj->get('id') )));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log('contact_save', array( 'success' => false )) ;
			
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
	 * Remove o registro selecionado
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
			$app['audit']->log('contact_remove', array( 'success' => true, 'data' => \SiMCE\Classes\Contatos::getByID( $id )));
			
			\SiMCE\Classes\Contatos::remove($id);
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch( \Exception $e ) {
			
			// Auditoria
			$app['audit']->log('contact_remove', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Ocorreu um erro ao remover o registro selecionado!"
			));
			
		}
		
		
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
			$arr = \SiMCE\Classes\Contatos::getByQuery(" cpf = :cpf ", array( "cpf" => $cpf ));
		else
			$arr = \SiMCE\Classes\Contatos::getByQuery(" cpf = :cpf AND id != :id ", array( "cpf" => $cpf, "id" => $id ));

		return $app->json( array(
			'success' => true,
			'content' => count($arr) ? array_shift($arr) : false
		));

	}

}

?>
