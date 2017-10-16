<?php

/**
 * Controlador responsável por manipular a Timeline do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Timeline;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		$app->match( $prefix ,              array(__CLASS__,'index') );
		$app->match( $prefix . "view/",     array(__CLASS__,'view')  );
		$app->match( $prefix . "form/",     array(__CLASS__,'form'));
		$app->match( $prefix . "save/",     array(__CLASS__,'save'));
		$app->match( $prefix . "resource/", array(__CLASS__,'resource'));
	}
	
	/**
	 * Exibe a tela inicial
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
				'onLoad'    => false,
				'functions' => $app['twig']->render( basename(__DIR__) . '/Views/functions.js' ),
				'onUnload'  => false
			)
		);
			
	}
	
	/**
	 * Monta o conteúdo da timeline
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function view( \Silex\Application $app ) {
		
		// Cria a configuração da timeline
		$config = array(
			"timeline" => array(
				"type" => "default",
				"date" => array()
			)
		);

		// Obtem os dados da operação
		$operation = \SiMCE\Classes\Operacoes::getByID( $app['request']->get('operacao'), true );
		$operation->bean->ownAlvos;
		
		//=============================
		//
		// Adiciona o período inicial
		//
		//=============================
		$config["timeline"]["date"][] = array(
			//"headline"  => "Operação " . $operation->get('nome') . " - Início",
			"headline"  => "Início da Operação",
			"text"      => $app['twig']->render( basename(__DIR__) . '/Views/inicio.html', array( 'obj' => $operation ) ),
			"startDate" => str_replace("-",",", $operation->bean->inicio ),
			"endDate"   => str_replace("-",",", $operation->bean->inicio ),
			"tag"       => "<span class='cus-cog'></span> Eventos"
		);
		
		//====================================================
		//
		// Obtem os registros marcados com a flag de timeline
		//
		//====================================================
		$records = \SiMCE\Classes\Registros::getByQuery(
			" unidades_id = :id AND operacoes_id = :operacao AND timeline = 1 ORDER BY data DESC ",
			array(
				"id"       => $app['user']->getUnidade(),
				"operacao" => $app['request']->get('operacao')
			)
		);
		
		$curDate = null;
		$tRecords = array();
		
		foreach( $records as $row ) {
			
			// Obtem a data do registro
			list ($date, $hour) = explode(" ", $row['orig_data']);
			
			// Ajusta o alvo
			$row['alvo'] = \SiMCE\Classes\Alvos::getByID( $row['alvos_id'], true )->get('nome');

			// Verifica se a data mudou
			if ($curDate != $date) {

				// Adiciona os regitros na timeline
				if ($curDate !== null) {
					
					$config["timeline"]["date"][] = array(
						"headline"  => "Interceptações Importantes",
						"text"      => $app['twig']->render( basename(__DIR__) . '/Views/registros.html', array( 'rows' => $tRecords ) ),
						"startDate" => str_replace("-",",", $curDate ),
						"endDate"   => str_replace("-",",", $curDate ),
						"tag"       => "<span class='cus-table-multiple'></span> Interceptações"
					);
					
				}

				// Zera o array e adiciona o novo registro
				$tRecords = array( $row );
				
				
			} else {
				
				$tRecords[] = $row;
				
			}
			
			$curDate = $date;
			
		}
		
		$config["timeline"]["date"][] = array(
			"headline"  => "Interceptações Importantes",
			"text"      => $app['twig']->render( basename(__DIR__) . '/Views/registros.html', array( 'rows' => $tRecords ) ),
			"startDate" => str_replace("-",",", $curDate ),
			"endDate"   => str_replace("-",",", $curDate ),
			"tag"       => "<span class='cus-table-multiple'></span> Interceptações"
		);
		
		//====================================================
		//
		// Obtem os elementos adicionados posteriormente
		//
		//====================================================
		$elements = \SiMCE\Classes\TimelineElement::getByQuery(
			" operacoes_id = :operacao ",
			array ( "operacao" => $app['request']->get('operacao') )
		);
		foreach( $elements as $el ) {
			
			// Verifica o tipo
			$firstBytes = substr( $el['arquivo'], 0, 40 );
			$curDate = $el["data"];

			//============================
			//
			// Imagem 
			//
			//============================
			if (preg_match("/image/", $firstBytes)) {
				
				$config["timeline"]["date"][] = array(
					"headline"  => $el["nome"],
					"text"      => $app['twig']->render( basename(__DIR__) . '/Views/imagem.html', array( 'id' => $el["id"], 'descricao' => $el['descricao'] ) ),
					"startDate" => str_replace("-",",", $curDate ),
					"endDate"   => str_replace("-",",", $curDate ),
					"tag"       => "<span class='cus-images'></span> Imagens"
				);
				
			}
			
			//============================
			//
			// Audio
			//
			//============================
			else if (preg_match("/audio/", $firstBytes)) {
				
				$config["timeline"]["date"][] = array(
					"headline"  => $el["nome"],
					"text"      => $app['twig']->render( basename(__DIR__) . '/Views/audio.html', array( 'id' => $el["id"], 'descricao' => $el['descricao'] ) ),
					"startDate" => str_replace("-",",", $curDate ),
					"endDate"   => str_replace("-",",", $curDate ),
					"tag"       => "<span class='cus-music'></span> Áudios"
				);
				
			}
			
			//============================
			//
			// Vídeo
			//
			//============================
			else if (preg_match("/video/", $firstBytes)) {
				
				$config["timeline"]["date"][] = array(
					"headline"  => $el["nome"],
					"text"      => $app['twig']->render( basename(__DIR__) . '/Views/video.html', array( 'id' => $el["id"], 'descricao' => $el['descricao'] ) ),
					"startDate" => str_replace("-",",", $curDate ),
					"endDate"   => str_replace("-",",", $curDate ),
					"tag"       => "<span class='cus-film'></span> Vídeos"
				);
				
			}
			
			//=================================
			//
			// Apenas Texto
			//
			//=================================
			else if (empty($el["arquivo"])) {
				
				$config["timeline"]["date"][] = array(
					"headline"  => $el["nome"],
					"text"      => $el["descricao"],
					"startDate" => str_replace("-",",", $curDate ),
					"endDate"   => str_replace("-",",", $curDate ),
					"tag"       => "<span class='cus-page-white-stack'></span> Documentos em Geral"
				);
				
			}
			
			//=================================
			//
			// Outros Arquivos
			//
			//=================================
			else {
				
				$config["timeline"]["date"][] = array(
					"headline"  => $el["nome"],
					"text"      => $app['twig']->render( basename(__DIR__) . '/Views/arquivo.html', array( 'id' => $el["id"], 'descricao' => $el['descricao'] ) ),
					"startDate" => str_replace("-",",", $curDate ),
					"endDate"   => str_replace("-",",", $curDate ),
					"tag"       => "<span class='cus-page-white-stack'></span> Documentos em Geral"
				);
				
			}
			
			
		}
		
		//=============================
		//
		// Adiciona o período final
		//
		//=============================
		$config["timeline"]["date"][] = array(
			//"headline"  => "Operação " . $operation->get('nome') . " - Fim",
			"headline"  => "Fim da Operação",
			"text"      => $app['twig']->render( basename(__DIR__) . '/Views/fim.html', array( 'obj' => $operation ) ),
			"startDate" => str_replace("-",",", $operation->bean->fim ),
			"endDate"   => str_replace("-",",", $operation->bean->fim ),
			"tag"       => "<span class='cus-cog'></span> Eventos"
		);
		
		//=============================
		//
		// Retorna a configuração
		//
		//=============================
		
		// Auditoria
		$app['audit']->log( 'timeline_load', array( 'success' => true, 'data' => $app['request']->get('operacao') ));
		
		return $app->json( $config );
		
	}

	/**
	 * Retorna o form de cadastro
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function form( \Silex\Application $app ) {
		
		return $app['twig']->render( basename(__DIR__) . '/Views/form.html' );

	}
	
	/**
	 * Salva o elemento na timeline
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
			$obj = new \SiMCE\Classes\TimelineElement();
			$obj->setAll( $data );
			
			// Ajusta o arquivo e obtem o conteúdo
			if ( !empty($data['arquivo'])) {
				$obj->set('arquivo', $app['session']->get( $data['arquivo'] ));
				$app['session']->set( $data['arquivo'], NULL );
			}
			
			$operacao = \SiMCE\Classes\Operacoes::getByID( $app['request']->get('operacao'), true );
			$obj->set( 'operacoes', $operacao->bean );
			$obj->save();
			
			// Auditoria
			$app['audit']->log( 'timeline_save', array( 'success' => true, 'data' => array( 'operacao' => $app['request']->get('operacao'), 'obj' => $data )));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log( 'timeline_save', array( 'success' => false ));
			
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
	 * Faz o preview do elemento
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function resource( \Silex\Application $app ) {
		
		// Obtem o elemento informado
		$id = $app['request']->get('id');
		$el = \SiMCE\Classes\TimelineElement::getByID($id);
			
		// Obtem o mime type e o resto do conteúdo
		if (preg_match("/data:([^;]+);base64,(.*)$/", $el["arquivo"],$reg)) {
			
			// Cabeçalhos
			$headers["Content-Type"] = $reg[1];
			if (!preg_match("/image/", $headers["Content-Type"])) {
				$headers["Content-Disposition"] = 'attachment; filename="' . uniqid("", true) . '"';
				$headers["Content-Length"]      = strlen(base64_decode($reg[2]));
			}
			
			return new \Symfony\Component\HttpFoundation\Response(
				base64_decode($reg[2]),
				200,
				$headers
			);
			
		}
		
		return "";
		
	}
	
}

?>