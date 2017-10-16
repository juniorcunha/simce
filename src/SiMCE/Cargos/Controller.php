<?php

/**
 * Controlador responsável por manipular os cargos e perfis
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Cargos;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		$app->match( $prefix ,            array(__CLASS__,'index')   );
		$app->match( $prefix . "form/" ,  array(__CLASS__,'form')    );
		$app->match( $prefix . "save/" ,  array(__CLASS__,'save')    );
		$app->match( $prefix . "remove/", array(__CLASS__,'remove')  );
		$app->match( $prefix . "list/" ,  array(__CLASS__,'getList') );
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
		if (!empty($id)) {
			$data = \SiMCE\Classes\Cargos::getByID( $id );
			$data["roles"] = explode(",", str_replace('"','', $data["acao"]));
		}

		// Auditoria
		$app['audit']->log('profile_form', array( 'success' => true, 'data' => $data ));
		
		return $app['twig']->render( basename(__DIR__) . '/Views/form.html', array( 'data' => $data ) );

	}
	
	/**
	 * Retorna a lista de registros cadastrados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getList(\Silex\Application $app ) {
		
		// Obtem a lista de beans de acordo com o tipo do usuário, com resultados paginados
		$obj = \SiMCE\Classes\Cargos::getByPage(
			" unidades_id = :id ",
			array( "id" => $app['user']->getUnidade() ),
			$app["request"]->get("iDisplayStart"),
			$app["request"]->get("iDisplayLength")
		);
		
		// Auditoria
		$app['audit']->log('profile_list', array( 'success' => true ));
		
		// Retorna as dados
		return $app->json( array(
			'aaData'               => $obj->rows,
			'iTotalRecords'        => $obj->total,
			'iTotalDisplayRecords' => $obj->total,
			'sEcho'                => $app['request']->get('sEcho')
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
		$data = $app['request']->get('data');
		$data['acao'] = json_encode($data['acao']);
		
		// Cria um objeto para armazenar as informações
		try {
			
			$obj = new \SiMCE\Classes\Cargos();
			$obj->setAll( $data );
			$unidade = \SiMCE\Classes\Unidades::getByID( $app['user']->getUnidade(), true );
			$obj->set( 'unidades', $unidade->bean );
			$obj->save();

			// Auditoria
			$app['audit']->log('profile_save', array( 'success' => true, 'data' => $data ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log('profile_save', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Por favor, verifique o preenchimento dos campos marcados!",
				'fields'  => $obj->invalidFields
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
			$app['audit']->log('profile_remove', array( 'success' => true, 'data' => \SiMCE\Classes\Cargos::getByID($id) ));
			
			\SiMCE\Classes\Cargos::remove($id);
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch( \Exception $e ) {
			
			// Auditoria
			$app['audit']->log('profile_save', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Ocorreu um erro ao remover o registro selecionado!"
			));
			
		}
		
		
	}
	
}

?>