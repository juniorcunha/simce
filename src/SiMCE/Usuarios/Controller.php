<?php

/**
 * Controlador responsável por manipular os usuários do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Usuarios;

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
	 * Lista as unidades organizacionais cadastradas
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
		if (!empty($id))
			$data = \SiMCE\Classes\Usuarios::getByID( $id );

		// Auditoria
		$app['audit']->log( 'user_form', array( 'success' => true, 'data' => $data ));
		
		return $app['twig']->render( basename(__DIR__) . '/Views/form.html', array( 'data' => $data ) );

	}
	
	/**
	 * Retorna a lista de usuários cadastrados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getList(\Silex\Application $app ) {
		
		// Retorna os Usuários
		$obj = array();
		if ( $app['user']->isSuperAdmin() )  {
			$obj = \SiMCE\Classes\Usuarios::getByPage(
				' tipo = :tipo ',
				array(
					'tipo' => 'A'
				),
				$app["request"]->get("iDisplayStart"),
				$app["request"]->get("iDisplayLength")
			);			
		} else if ( $app['user']->isAdmin() )  {
			$obj = \SiMCE\Classes\Usuarios::getByPage(
				' tipo = :tipo AND unidades_id = :id ',
				array(
					'tipo' => 'O',
					'id'   => $app['user']->getUnidade()
				),
				$app["request"]->get("iDisplayStart"),
				$app["request"]->get("iDisplayLength")
			);			
		}
		
		// Auditoria
		$app['audit']->log( 'user_list', array( 'success' => true, 'data' => $app['user']->getUnidade() ));
		
		// Retorna os Usuários
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
		
		// Cria um objeto para armazenar as informações
		try {
			
			$usuario = new \SiMCE\Classes\Usuarios();
			$usuario->setAll( $data );
			
			// Verifica se o usuário é um administrador
			if ( $app['user']->isAdmin() ) {
				// Obtem a unidade e adiciona no novo usuário
				$usuario->set('unidades_id', $app['user']->get('unidades_id'));
			}
			
			$usuario->save();

			// Auditoria
			$app['audit']->log( 'user_save', array( 'success' => true, 'data' => $data ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log( 'user_save', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Por favor, verifique o preenchimento dos campos marcados!",
				'fields'  => $usuario->invalidFields,
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
			$app['audit']->log( 'user_remove', array( 'success' => true, 'data' => \SiMCE\Classes\Usuarios::getByID($id) ));
			
			\SiMCE\Classes\Usuarios::remove($id);
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch( \Exception $e ) {
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Ocorreu um erro ao remover o registro selecionado!"
			));
			
		}
		
		
	}
	
}

?>
