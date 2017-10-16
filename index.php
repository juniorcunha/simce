<?php

/**
 * Arquivo de carregamento principal. Se encarrega
 * de carregar o framework Silex e definir os objetos
 * que serão utilizados no projeto.
 * 
 * @author Junior Cunha <juniorcunha@dad.eng.br>
 * @copyright (c) 2013, dad Engenharia
 */

set_time_limit(0);
include_once( __DIR__ . '/vendor/autoload.php' );
include_once( __DIR__ . '/libs/redbean/rb.php' );

define("SIMCE_DIR", __DIR__);
define("SIMCE_CACHE_DIR", SIMCE_DIR . "/cache/");
define("AST_SPOOL_DIR", "/var/spool/asterisk/monitor/");

/**
 * Inicializa a aplicação
 */
$app = new \Silex\Application();
$app['version'] = "2.4.1";

/**
 * Registra o arquivo de configuração
 */
$app['config'] = parse_ini_file( __DIR__ . "/config.ini", true );


/**
 * Registra o namespace SiMCE
 */
$cl = new \Composer\Autoload\ClassLoader();
$cl->add('SiMCE', __DIR__ . '/src/');
$cl->register();

/**
  * Registra os providers do framework
 */

// Twig
$app->register(
	new \Silex\Provider\TwigServiceProvider(),
	array(
		'twig.path'    => __DIR__ . '/src/SiMCE/',
		'twig.options' => array(
			'strict_variables' => false
		)
	)
);

// Session
$app->register(new Silex\Provider\SessionServiceProvider());

/**
 * Registra função para retornar usuário autenticado
 * 
 * @var \SiMCE\Classes\Contatos
 */
$app['user'] = function( $app ) {
	return $app['session']->get('SIMCE_USER');
};

/**
 * Registra as rotas utilizadas no sistema
 */
\SiMCE\Core\Controller::factory($app,"/");
\SiMCE\Dashboard\Controller::factory($app, "/dashboard/");
\SiMCE\Unidades_Organizacionais\Controller::factory($app,"/und_org/");
\SiMCE\Recursos_Interceptacao\Controller::factory($app, "/rec_intr/");
\SiMCE\Usuarios\Controller::factory($app, "/usuarios/");
\SiMCE\Operacoes\Controller::factory($app, "/operacoes/");
\SiMCE\Cargos\Controller::factory($app, "/cargos/");
\SiMCE\Alvos\Controller::factory($app, "/alvos/");
\SiMCE\Interceptacoes\Controller::factory($app, "/interceptacoes/");
\SiMCE\Contatos\Controller::factory($app, "/contatos/");
\SiMCE\Timeline\Controller::factory($app, "/timeline/");
\SiMCE\Network\Controller::factory($app, "/network/");
\SiMCE\Dicionario\Controller::factory($app, "/ontologias/");
\SiMCE\Relatorios\Controller::factory($app, "/relatorios/");
\SiMCE\Backup\Controller::factory($app, "/backup/");
\SiMCE\Configuracoes\Controller::factory($app, "/configuracoes/");
\SiMCE\Alocacoes\Controller::factory($app, "/alocacoes/");

/**
 * Faz a verificação de erros
 */
$app->error(function (\Exception $e, $code) use ($app) {
	return \SiMCE\Core\Controller::error($app, $e, $code);
});

/**
 * Faz o direcionamento para a página de login caso necessário
 */
$app->before( function (\Symfony\Component\HttpFoundation\Request $request) use ($app) {
	if (!preg_match("/login|logout|do_login|network/", $request->getRequestUri())) {
		if ($app['user'] === null) {
			return new \Symfony\Component\HttpFoundation\RedirectResponse('/simce/login');
		}
	}
	
	// Inicia o log das querys
	$app['queryLogger'] = \RedBean_Plugin_QueryLogger::getInstanceAndAttach(
        R::getDatabaseAdapter()
    );
	
});

/**
 * Faz o log das queries executadas
 */
/*$app->after(function (\Symfony\Component\HttpFoundation\Request $request, Symfony\Component\HttpFoundation\Response $response) {
	global $app;
	foreach( $app['queryLogger']->getLogs() as $query ) {
		$app['debug']->log( "ui::" . $app['user']->get('login'), $query );
	}
});*/

/**
 * Configura a conexão do ReadBean
 */
\R::setup(
	$app['config']['database']['driver'] . ":" . 
	"host=" . $app['config']['database']['host'] . ";" . 
	"dbname=" . $app['config']['database']['db'],
	$app['config']['database']['user'],
	$app['config']['database']['password']
);
//\R::freeze( true );

/**
 * Configura a conexão com o Xplico
 */
//$app['xplico'] = new PDO("sqlite:/opt/xplico/xplico.db");

/**
 * Registra o serviço de acesso ao asterisk
 * 
 * @var \SiMCE\Classes\AsteriskManager
 */
$app['asterisk'] = $app->share( function( $app ) {
	
	// Cria a instância do AsteriskManager
	$ast = new \SiMCE\Classes\AsteriskManager(
		array(
			'server' => $app['config']['asterisk']['host'],
			'port'   => $app['config']['asterisk']['port']
		)
	);

	// Faz a conexão e efetua o login
	$ast->connect();
	$ast->login(
		$app['config']['asterisk']['user'],
		$app['config']['asterisk']['password']
	);
	
	return $ast;
	
});

/**
 * Registra a função de debug
 * 
 * @var \SiMCE\Classes\Debug
 */
$app['debug'] = $app->share( function( $app ) {
	global $app;
	$debug = new \SiMCE\Classes\Debug();
	return $debug;
});

/**
 * Registra a função de auditoria
 * 
 * @var \SiMCE\Classes\Audit
 */
$app['audit'] = $app->share( function( $app ) {
	global $app;
	$audit = new \SiMCE\Classes\Audit();
	return $audit;
});

/**
 * Executa a aplicação
 */
if (php_sapi_name() != 'cli')
	$app->run();

?>
