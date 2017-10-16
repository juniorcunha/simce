<?php

/**
 * Controlador responsável por manipular os recursos
 * de interceptação
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Recursos_Interceptacao;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		$app->match( $prefix ,           array(__CLASS__,'index') );
		$app->match( $prefix  . "form/", array(__CLASS__,'form')  );
		$app->match( $prefix  . "save/", array(__CLASS__,'save')  );
	}
	
	/**
	 * Exibe o formulário inicial para 
	 * definir os recursos de interceptação
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function index( \Silex\Application $app ) {
		
		// Obtem a lista das unidades
		$unidades = \SiMCE\Classes\Unidades::getAll();
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'error'     => false,
				'content'   => $app['twig']->render( basename(__DIR__) . '/Views/index.html', array( 'unidades' => $unidades ) ),
				'onLoad'    => false,
				'functions' => $app['twig']->render( basename(__DIR__) . '/Views/functions.js' ),
				'onUnload'  => false
			)
		);
			
	}
	
	/**
	 * Exibe o formulário com os recursos e administradores da ferramenta
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function form( \Silex\Application $app ) {
		
		// Obtem a lista de recursos disponíveis
		$id = $app['request']->get('id');
		$recursos = \SiMCE\Classes\Recursos::getAll();
		
		// Obtem a lista dos usuários
		$usuarios = \SiMCE\Classes\Usuarios::getByQuery(
			' tipo = :tipo ',
			array( 'tipo' => 'A' )
		);
		
		// Auditoria
		$app['audit']->log( 'resources_form', array( 'success' => true, 'data' => $id ));
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'content'   => $app['twig']->render(
					basename(__DIR__) . '/Views/form.html',
					array( 'id' => $id, 'recursos' => $recursos, 'usuarios' => $usuarios )
				)
			)
		);
			
	}
	
	/**
	 * Salva as informações selecionadas na tela
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function save( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data = $app['request']->get('data');
		
		// Cria um objeto para armazenar as informações
		try {
			
			// Obtem o item da unidade
			$unidade = \SiMCE\Classes\Unidades::getByID( $data['unidade'], true );
			$arrRecursos = array();
			foreach( $data['audio'] as $r_id ) {
				$arrRecursos[] = \SiMCE\Classes\Recursos::getByID( $r_id, true )->bean;
			}
			foreach( $data['gsm'] as $r_id ) {
				$arrRecursos[] = \SiMCE\Classes\Recursos::getByID( $r_id, true )->bean;
			}
			foreach( $data['dados'] as $r_id ) {
				$arrRecursos[] = \SiMCE\Classes\Recursos::getByID( $r_id, true )->bean;
			}

			// Remove o administrador atual e redefine o parentesco
			$arrAdmin = \SiMCE\Classes\Usuarios::getByQuery(
				' unidades_id = :unidade AND tipo = :tipo ',
				array(
					'unidade' => $data['unidade'],
					'tipo'    => 'A'
				),
				true
			);
			foreach( $arrAdmin as $admin ) {
				$admin->set('password2',$admin->get('password'));
				$admin->set('unidades_id',null);
				$admin->save();
			}
			
			// Define o usuário administrador
			if ( $data['admin'] ) {
				$admin = \SiMCE\Classes\Usuarios::getByID( $data['admin'], true );
				$admin->set('password2',$admin->get('password'));
				$admin->set('unidades', $unidade->bean);
				$admin->save();
			}
			
			// Sava os dados na unidade
			$unidade->set('ownRecursos', $arrRecursos );
			$unidade->save();

			// Auditoria
			$app['audit']->log( 'resources_save', array( 'success' => true, 'data' => $data ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log( 'resources_save', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Por favor, verifique o preenchimento dos campos marcados!",
				'fields'  => $unidade->invalidFields
			));
			
		}
			
	}
	
}

?>