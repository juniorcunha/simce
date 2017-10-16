<?php

/**
 * Controlador responsável pela interface do usuário
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Core;

class Controller {

	// ChangeLog
	static private $changelog = array(

		'2.4.1 (18/01/2017)' => array(
			'Suporte para verificar os relatos e observações diretamente na listagem dos registros.',
			'Modificação no evento de escuta do registro na listagem para exibir de forma diferenciada no Relatório de Auditoria.',
			'Novos filtros por Usuário, IP e Ação no Relatório de Auditoria.'
		),
		'2.4.0 (01/07/2016)' => array(
			'Adicionada paginação nos relatórios de Auditoria e Acesso.',
			'Ajuste para que o usuário não possa trocar o seu nome nas opções de perfil.',
			'Lista de operações só exibe aquelas que o usuário é responsável por algum alvo.',
			'Melhoria no nome do arquivo gerado pela exportação e pelo Relatório Interativo para conter o nome da operação e o período de início e fim.',
			'Adicionado filtro por IP e usuário no Relatório de Acesso.',
			'Novo relatório de Distribuição de Canais para exibir de forma simples a relação alvos x canais x responsáveis.',
			'Visualização das alocações ativas no Dashboard (Somente para Administradores).',
			'Novo módulo Recursos de Interceptação para visualizar a utilização dos recursos e como estão distribuiídos no sistema.',
			'Novo relatório de Desvios que identifica as ligações no modo siga-me para os responsáveis por cada alvo.'
		),

		'2.3.2 (29/04/2016)' => array(
			'Link para manual do produto diretamente na barra superior.'
		),

		'2.3.1 (10/03/2016)' => array(
			'Melhorias no Relatório de Auditoria agora com informações detalhadas mais objetivas.',
			'Suporte para obter os dados do Alvo/Interlocutor de outra operação baseado no cpf.'
		),

		'2.3.0 (01/03/2016)' => array(
			'Melhorias na exportação de registros com novas informações exibidas e acompanhamento do andamento do processo.',
			'Reformulação do Relatório de Operação com informações análiticas e detalhadas sobre o processo investigativo.',
			'Relatório Interativo com suporte a filtros avançados e controle de execução.',
			'Novo relatório de acesso contendo informações de login/logout dos usuários do sistema.',
			'Melhorias gerais de usabilidade e performance.'
		),

		'2.2.0 (30/12/2015)' => array(
			'Suporte para informações bancárias para alvos e interlocutores.',
			'Suporte para exportar a Rede de Relacionamento para um arquivo externo.',
			'Suporte para importar transcrição e identificação de interceptações no Relatório Judicial.',
			'Suporte para importar a Rede de Relacionamentos no Relatório Judicial.',
			'Suporte para reprodução das interceptações diretamente na Cronologia de Eventos.',
			'Melhorias na utilização dos relatórios.',
			'Modificação nos campos de telefone para suportar 9 digitos.'
		),
	
		'2.1.0 (10/04/2015)' => array(
			'Busca direta pelo ID na barra superior.',
			'Filtros avançados no módulo de Interceptações por Interlocutor, "Com Transcrição" e "Com Observação".',
			'Exibição do gráfico de áudio e identificação diretamente na listagem.',
			'Melhorias na performance para visualização dos detalhes das Interceptações.',
			'Melhorias na performance no módulo de Rede de Relacionamento exibindo somente interlocutores identificados.',
			'Visualização dos relacionamentos entre Alvos e Interlocutores com um único clique no módulo de Rede de Relacionamentos.',
			'Novo suporte para filtros na Rede de Relacionamentos permite maior granularidade na análise dos dados.',
			'Novas informações cadastrais para Alvos e Interlocutores permite maior detalhamento sobre os investigados.',
			'Melhorias e correções gerais de usabilidade e performance.',
			'Exportação de registros para formato ZIP diretamente no módulo de Interceptações.',
			'Suporte para classificação do registro diretamente na listagem.',
			//'Exportação da rede de relacionamento.'
		),
		
		'2.0.7 (07/09/2014)' => array(
			'Ajuste no timeout das requisições AJAX.',
			'Correção na função que calcula o tempo nos relatórios.',
			'Correção na adição de novos elementos no módulo de Cronologia de Eventos.',
			'Correção no módulo de Gestão de Recursos das Unidades Organizacionais.',
		),
		
		'2.0.6 (29/07/2014)' => array(
			'Mudança nos detalhes das interceptações para que não carregue todas as informações de uma única vez.',
			'Exibição do changelog no topo de página.'
		),

		'2.0.5 (21/07/2014)' => array(
			'Melhorias nos Dashboards para exibir corretamente as informações de sistema.',
			'Auditoria reformulada com ajustes na tradução dos eventos.',
			'Novo logo para tela de login.',
			'Flexibilização dos parâmetros necessários para Alvos e Interlocutores.',
			'Correção do bug para edição do período da operação.',
			'Modificação no serviço de voz para detectar processos zumbis.'
		),

		'2.0.4 (12/07/2014)' => array(
			'Melhora substancial no mecanismo de aprendizagem de voz.',
			'Novos gráficos no relatório de Sistema.',
			'Novos scripts de controle de uso do disco.',
			'Correção para a tela de configurações que não estava gravando as informações corretamente.'
		),

		'2.0.3 (17/06/2014)' => array(
			'Informação de carregamento em janelas com conteúdo muito grande.',
			'Correção na exibição do status de canais de dados.'
		),

		'2.0.2 (04/06/2014)' => array(
			'Ajustes na performance do processamento da biometria de voz.',
		),

		'2.0.1 (30/05/2014)' => array(

			'Novos scripts de monitoração.',
			'Melhora no processo de debug.'
		),

		'2.0.0 (10/05/2014)' => array(
			'Interface WEB com novo visual garantindo maior agilidade e usabilidade.',
			'Suporte a multiplas unidades organizacionais no mesmo equipamento.',
			'Dashboards para acompanhamento das operações e a saúde do sistema.',
			'Gestão de Alvos e Interlocutores.',
			'Biometria de Voz.',
			'Rede de Relacionamentos para identificação de tendências.',
			'Cronologia de Eventos para visualização objetiva dos acontecimentos importantes.',
			'Relatório Judicial totalmente modular e customizável.',
			'Monitoração dos feixes E1, canais GSM e conectividade com internet.',
			'Desvio de chamadas para telefones em campo.'
		)

	);
	
	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		$app->match( $prefix . "/",           array(__CLASS__,'index'));
		$app->match( $prefix . "login/",      array(__CLASS__,'login'));
		$app->match( $prefix . "do_login/",   array(__CLASS__,'doLogin'));
		$app->match( $prefix . "logout/",     array(__CLASS__,'logout'));
		$app->match( $prefix . "changelog/",  array(__CLASS__,'changelog'));
		$app->match( $prefix . "upload/",     array(__CLASS__,'upload'));
		$app->match( $prefix . "uploadForm/", array(__CLASS__,'uploadForm'));
		$app->match( $prefix . "user_events/",array(__CLASS__,'userEvents'));
		$app->match( $prefix . "user_profile/",array(__CLASS__,'userForm'));
		$app->match( $prefix . "save_user_profile/",array(__CLASS__,'saveUserForm'));
		$app->match( $prefix . "find_id/",array(__CLASS__,'findID'));
	}
	

	/**
	 * Trata os erros para a interface
	 * 
	 * @param \Silex\Application $app
	 * @param \Exception $e
	 * @param int $code
	 * @return string
	 */
	static function error( \Silex\Application $app, \Exception $e, $code ) {
		
		// Verifica se é uma requisição AJAX
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return $app->json( array(
				'success' => false,
				'error'   => false,
				'dump'    => print_r( $app['queryLogger']->getLogs(), true ),
				'content' => $app['twig']->render(
					basename(__DIR__) . '/Views/error.html',
					array(
						'code' => $code,
						'msg'  => $e->getMessage()
					)
				)
			), 200, array('X-Status-Code' => 200));
		} else { // Requisição normal
			return $app['twig']->render(
				basename(__DIR__) . '/Views/error.html',
				array(
					'code' => $code,
					'msg'  => $e->getMessage()
				)
			);
		}
	}
	
	/**
	 * Interface principal
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function index( \Silex\Application $app ) {

		// Obtem a lista de operações
		$opList = array();
		if ( $app['user']->isSuperAdmin() === false ) {
			$opList = \SiMCE\Classes\Operacoes::getByQuery(
				" unidades_id = :id ORDER BY `nome` ",
				array( "id" => $app['user']->getUnidade() )
			);

			if ($app['user']->isAdmin() === false) {
				foreach( $opList as $idx => $op ) {
					$ret = array_pop(\R::getAll("SELECT count(*) as count FROM permissoes WHERE alvos_id IN (SELECT id FROM alvos WHERE operacoes_id = {$op['id']}) AND usuarios_id = " . $app['user']->get('id')));
					if ($ret['count'] == 0)
						unset($opList[$idx]);
				}
			}
		}
		
		return $app['twig']->render(
			basename(__DIR__) . '/Views/index.html',
			array(
				'opList' => $opList
			)
		);
	}
	
	/**
	 * Exibe a página de login
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function login( \Silex\Application $app ) {
	
		// Verifica se o usuário já não está autenticado
		if ($app['user'] !== null) {
			return new \Symfony\Component\HttpFoundation\RedirectResponse('/simce/');
		}
		
		return $app['twig']->render( basename(__DIR__) . '/Views/login.html', array( 'cookie' => json_decode(base64_decode($_COOKIE["SiMCE"]))));
	}
	
	/**
	 * Faz o processo de autenticação
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function doLogin( \Silex\Application $app ) {
		
		// Obtem os dados do form
		$data = $app['request']->get('data');
		
		// Busca registro no banco com os dados informados
		$search = \SiMCE\Classes\Usuarios::getByQuery(
			" login = :login AND password = :password",
			array(
				"login"    => $data['login'],
				"password" => md5($data['password'])
			),
			true
		);
		
		// Autenticação com sucesso
		if (count($search)) {
			
			// Armazena o cookie caso tenha solicitado lembrar os dados
			$header = array();
			if ($data['remember'] == 1) {
				$header = array( 'Set-Cookie' => 'SiMCE=' . base64_encode(json_encode($data)) . "; path=/;");
			} else {
				$header = array( 'Set-Cookie' => 'SiMCE=; path=/; expires=' . date("r", time()-3600));
			}
			// Armazena na sessão os dados do usuário
			$obj = array_pop( $search );
			
			// Verifica se o usuário administrador possui alguma unidade definida
			if (!$obj->isSuperAdmin() && $obj->get('unidades_id') == null) {
				return $app->json( array(
					'success' => false,
					'error'   => "Este usuário não foi alocado para nenhuma unidade. Por favor, entre em contato com o administrador."
				));
			}

			// Verifica se o usuário já esta online
			if ($obj->get('online') == 1) {
				return $app->json( array(
					'success' => false,
					'error'   => "Este usuário já está online no IP " . $obj->get('last_ip') . 
								 ", desde as " . \SiMCE\Classes\DataRecord::dbToDateTime($obj->get('last_login')) . 
								 ". Por favor, entre em contato com o administrador." 
				));
			}
			
			// Adiciona informações de status da sessão
			$obj->set('online', 1);
			$obj->set('password2', $obj->get('password'));
			$obj->set('last_login', date('Y-m-d H:i:s'));
			$obj->set('last_update',date('Y-m-d H:i:s'));
			$obj->set('last_ip', $_SERVER['REMOTE_ADDR']);
			$obj->save();
			
			$app['session']->set('SIMCE_USER', $obj);
			
			// Auditoria
			$app['audit']->log('user_login', array( 'success' => true ));
			
			return $app->json(
				array( 'success' => true ),
				200,
				$header
			);
			
		} else { // Erro na autenticação
			
			// Auditoria
			$app['audit']->log('user_login', array( 'success' => false ));
			
			return $app->json( array(
				'success' => false,
				'error'   => "Login e/ou Senha inválidos."
			));
			
		}
		
	}
	
	/**
	 * Faz o logout do usuário
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function logout( \Silex\Application $app ) {

		// Auditoria
		$app['audit']->log('user_logout', array( 'success' => true ));
		
		// Atualiza a sessão
		$obj = $app['session']->get('SIMCE_USER');
		$obj->set('online', 0);
		$obj->set('password2', $obj->get('password'));
		$obj->set('last_update',date('Y-m-d H:i:s'));
		$obj->save();
		
		// Reset na sessão
		$app['session']->set('SIMCE_USER', null);
		
		// Redireciona a resposta
		return new \Symfony\Component\HttpFoundation\RedirectResponse('/simce/');
		
	}
	
	/**
	 * Faz o upload do arquivo
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function upload( \Silex\Application $app ) {
		
		// Obtem os dados informados
		$id   = $app['request']->get('id');
		$data = $app['request']->get('data');
		
		// Atualiza a informação na sessão
		$app['session']->set($id, $app['session']->get($id) . $data);
		
		// Redireciona a resposta
		return $app->json(
			array(
				'success' => true
			)
		);
		
	}

	/**
	 * Retorna o form de upload
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function uploadForm( \Silex\Application $app ) {
		return $app['twig']->render( basename(__DIR__) . '/Views/upload.html' );

	}
	
	/**
	 * Retorna o form de upload
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function userEvents( \Silex\Application $app ) {

		// Atualiza a sessão
		$obj = $app['session']->get('SIMCE_USER');
		$obj->set('online', 1);
		$obj->set('password2', $obj->get('password'));
		$obj->set('last_update',date('Y-m-d H:i:s'));
		$obj->save();
		
		// Obtem a quantidade de novas ligações por operação
		$newCalls = 0;
		$newCallsArr = array();
		$operacoes = \SiMCE\Classes\Operacoes::getAll();
		foreach( $operacoes as $op ) {

			// Obtem os alvos que o usuário tem permissão de visualizar
			$targets = $app['user']->getTargetsByAction( $app['request']->get('operacao'), "ACTION_INTERCEPTION_" );

			$statement = null;
			$binds = array();
			if ($app['user']->isAdmin()) {
				$statement         = " unidades_id = :unidade AND operacoes_id = :operacao AND estado = :estado ";
				$binds["unidade"]  = $app['user']->getUnidade();
				$binds["operacao"] = $op['id'];
				$binds["estado"]   = \SiMCE\Classes\Registros::STATUS_NEW;
			} else {
				$statement         = " unidades_id = :unidade AND operacoes_id = :operacao AND alvos_id IN ( " . implode(",", $targets) . " )  AND estado = :estado ";
				$binds["unidade"]  = $app['user']->getUnidade();
				$binds["operacao"] = $op['id'];
				$binds["estado"]   = \SiMCE\Classes\Registros::STATUS_NEW;
			}
			
			// Contabiliza os registros
			$count = \SiMCE\Classes\Registros::getCount($statement, $binds);
			$newCalls += $count;
			if ($count)
				$newCallsArr[] = $op['nome'] . ": " . $count;
			
		}
		
		// Responde com sucesso
		return $app->json(
			array(
				'success'     => true,
				'newCalls'    => $newCalls,
				'newCallsMsg' => implode(",", $newCallsArr)
			)
		);

	}
	
	/**
	 * Retorna o form de cadastro do perfil
	 * de usuário
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function userForm( \Silex\Application $app ) {
	
		// Auditoria
		$app['audit']->log('user_profile', array( 'success' => true ));
		
		$id = $app['user']->get("id");
		$data = \SiMCE\Classes\Usuarios::getByID( $id );
		
		return $app['twig']->render( basename(__DIR__) . '/Views/userForm.html', array( 'data' => $data ) );

	}
	
	/**
	 * Salva as informações no banco de dados
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function saveUserForm( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data = $app['request']->get('data');
		
		// Cria um objeto para armazenar as informações
		try {

			$usuario = \SiMCE\Classes\Usuarios::getByID( $data['id'], true );
			$usuario->setAll( $data );
			$usuario->save();

			// Atualiza a sessão
			$app['session']->set('SIMCE_USER', $usuario);
			
			// Auditoria
			$app['audit']->log('user_profile_save', array( 'success' => true, 'data' => $data ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => true
			));
			
		} catch (\Exception $e) {
			
			// Auditoria
			$app['audit']->log('user_profile_save', array( 'success' => false ));
			
			// Informa a mensagem para o usuário
			return $app->json( array(
				'success' => false,
				'error'   => "Por favor, verifique o preenchimento dos campos marcados!" . $e->getMessage(),
				'fields'  => $usuario->invalidFields,
				'data'    => $data
			));
			
		}
		
	}
	
	/**
	 * Armazena o changelog do sistema
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function changelog( \Silex\Application $app ) {
		
		$details = $app['request']->get('details');
		list ($last_version,$version_date) = explode(" ", array_shift(array_keys( self::$changelog )));
		unset($version_date);
		$last_version = str_replace(".","_",$last_version);
		
		if (!empty($details)) { // Retorna a janela modal do changelog
			
			// Define o conteúdo do cookie
			$cookie = new \Symfony\Component\HttpFoundation\Cookie( "SiMCE_v_{$last_version}", $last_version );
			
			// Cria a resposta
			$response = new \Symfony\Component\HttpFoundation\Response(
				$app['twig']->render(
					basename(__DIR__) . '/Views/changelog.html',
					array(
						'data' => self::$changelog
					)
				)
			);
			$response->headers->setCookie($cookie);
			return $response;

		} else { // Retorna o conteúdo em formato json
						
			return $app->json( array(
				'success' => true,
				'log'     => self::$changelog,
				'new'     => ($app['request']->cookies->has( "SiMCE_v_{$last_version}" )) ? false : true,
				'ck'      => $_COOKIE
			));
			
		}
		
	}
	
	/**
	 * Busca os dados do registro informado
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function findID( \Silex\Application $app ) {

		return $app->json( array(
			'success'  => true,
			'record'   => \SiMCE\Classes\Registros::getByID( $app['request']->get('id') )
		));
		
	}	
	
}

?>
