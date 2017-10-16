<?php

/**
 * Controlador responsável por manipular as configurações gerais da unidade
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Configuracoes;

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
		$app->match( $prefix . "save/" ,  array(__CLASS__,'save')    );
	}
	
	/**
	 * Lista os registros cadastrados
	 * e permite inserir, editar e remover
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function index( \Silex\Application $app ) {
		
		// Obtem as configurações atuais
		$data = \SiMCE\Classes\Configuracoes::getByID( $app['user']->getUnidade() );
		
		// Cria uma informação inicial caso não existam registros
		if ($data['id'] == 0) {
			\R::exec(
				" INSERT INTO `configuracoes`(`unidades_id`) VALUES(:id) ",
				array( "id" => $app['user']->getUnidade() )
			);
		}

		// Auditoria
		$app['audit']->log('config_load', array( 'success' => true ));
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'error'     => false,
				'content'   => $app['twig']->render( basename(__DIR__) . '/Views/index.html', array( 'data' => $data ) ),
				'onLoad'    => false,
				'functions' => $app['twig']->render( basename(__DIR__) . '/Views/functions.js' ),
				'onUnload'  => false
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
		$data = $app['request']->get('data');
		
		// Cria um objeto para armazenar as informações
		try {
			
			$obj = new \SiMCE\Classes\Configuracoes();
			$obj->setAll( $data );
			$unidade = \SiMCE\Classes\Unidades::getByID( $app['user']->getUnidade(), true );
			$obj->set( 'unidades', $unidade->bean );
			$obj->save();
			
			// Auditoria
			$app['audit']->log('config_save', array( 'success' => true, 'data' => $data ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log('config_save', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Por favor, verifique o preenchimento dos campos marcados!",
				'fields'  => $obj->invalidFields
			));
			
		}
		
	}
	
}

?>