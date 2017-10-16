<?php

/**
 * Controlador responsável por manipular as unidades organizacionais
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Unidades_Organizacionais;

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
			$data = \SiMCE\Classes\Unidades::getByID( $id );

		// Auditoria
		$app['audit']->log( 'unit_form', array( 'success' => true, 'data' => $data ));
		
		return $app['twig']->render( basename(__DIR__) . '/Views/form.html', array( 'data' => $data ) );

	}
	
	/**
	 * Retorna a lista das unidades cadastradas
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function getList(\Silex\Application $app ) {
		
		// Obtem a lista de beans de acordo com resultados paginados
		$obj = \SiMCE\Classes\Unidades::getByPage(
			" ",
			array(),
			$app["request"]->get("iDisplayStart"),
			$app["request"]->get("iDisplayLength")
		);
		
		// Auditoria
		$app['audit']->log( 'unit_list', array( 'success' => true ));
		
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
		
		// Cria um objeto para armazenar as informações
		try {
			
			$unidade = new \SiMCE\Classes\Unidades();
			$unidade->setAll( $data );
			$unidade->save();
			
			// Auditoria
			$app['audit']->log( 'unit_save', array( 'success' => true, 'data' => $data ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log( 'unit_save', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Por favor, verifique o preenchimento dos campos marcados!",
				'fields'  => $unidade->invalidFields
			));
			
		}
		
	}
	
	/**
	 * Remove a unidade organizacional selecionada
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
			$app['audit']->log( 'unit_remove', array( 'success' => true, 'data' => \SiMCE\Classes\Unidades::getByID($id) ));
			
			\SiMCE\Classes\Unidades::remove($id);
			
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