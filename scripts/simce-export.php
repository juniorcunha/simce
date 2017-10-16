#!/usr/bin/php	
<?php

/**
 * SiMCE Export
 * 
 * Script responsável por exportar os registros conforme os dados informados
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2016
 */

set_time_limit(0);
ini_set('memory_limit', '-1');

declare(ticks = 1);
pcntl_signal(SIGHUP,  "sigHandler");
pcntl_signal(SIGTERM,  "sigHandler");

include_once( __DIR__ . '/../index.php' );

global $app;

// Define o nome do serviço
$serviceName = basename(__FILE__, ".php");

// Obtem o argumento via argv
$mode      = $argv[1]; // 0 - exportação, 1 - relatório interativo
$file_id   = $argv[2];
$binds     = (array) json_decode(base64_decode($argv[3]));
$statement = json_decode(base64_decode($argv[4]));
$opts      = (isset($argv[5])) ? json_decode(base64_decode($argv[5])) : false;

// Define as opções gerais do zip
// Caso tenha sido definida uma senha
$zOpts = false;
if (!empty($opts) && !empty($opts->password)) {
	//$zOpts = " -p'{$opts->password}' ";
	$zOpts = " --password '{$opts->password}' ";
}

// Define as variáveis globais do sistema
$statusFile = SIMCE_CACHE_DIR . "status-" . $file_id . ".status";
$zipFile =	SIMCE_CACHE_DIR . 
		(($mode == 0) ? "registros-" : "relatorio-") . 
		str_replace(" ", "_", \SiMCE\Classes\Operacoes::getByID( $binds['operacao'], true )->get('nome')) . "-" . 
		(!empty($binds['start']) ? $binds['start'] . "_" : null) .
		(!empty($binds['end']) ? $binds['end'] . "-" : null) .
		time() . ".zip";

// Cria um diretório temporário para facilitar a geração dos dados
updateStatus( "p", "Criando estrutura inicial", 0 );
$dir = SIMCE_CACHE_DIR . microtime(true) . "-" . uniqid("", true); 
@mkdir($dir);
@chdir($dir);

// Cria a estrutura de assets e imgs
@mkdir("assets");
exec("/bin/cp -r " . SIMCE_DIR . "/libs/bootstrap assets >/dev/null 2>/dev/null");
exec("/bin/cp -r " . SIMCE_DIR . "/libs/jquery assets >/dev/null 2>/dev/null");
exec("/bin/cp -r " . SIMCE_DIR . "/assets/img assets >/dev/null 2>/dev/null");

// Adiciona os assets ao arquivo zip
updateStatus( "p", "Adicionando assets", 0 );
//exec("/usr/bin/7za a -tzip {$zOpts} -mem=AES256 {$zipFile} * >/dev/null 2>/dev/null");
//exec("/usr/bin/zip {$zOpts} -u {$zipFile} * >/dev/null 2>/dev/null");


// Cria o diretório para os arquivos de áudio
@mkdir("files");

// Busca os registros conforme informações
//var_dump( memory_get_usage(true) );
$rows = \SiMCE\Classes\Registros::getByQuery( $statement, $binds, true );
//file_put_contents("/tmp/export.log", print_r($statement,true), FILE_APPEND);
//file_put_contents("/tmp/export.log", print_r($binds,true), FILE_APPEND);
//var_dump( count($rows) );
//var_dump( memory_get_usage(true) );
$total = 0;
foreach( $rows as &$row ) {

	$t1 = microtime(true);
	$total++;	
	updateStatus( "p", "Exportando ID {$row->get("id")}", $total );

	// Copia o arquivo para o diretório atual e adiciona no zip
	if (!empty($opts) && !empty($opts->mp3)) {
		exec("/usr/bin/ffmpeg -i " . $row->getFilePath() . " -acodec libmp3lame files/{$row->get('id')}.mp3 >/dev/null 2>/dev/null");
	} else {
		exec("/bin/cp " . $row->getFilePath() . " files >/dev/null 2>/dev/null");
	}

	exec("/bin/cp " . $row->getImagePath(true) . " files >/dev/null 2>/dev/null");

}

// Gera o arquivo html
updateStatus( "p", "Gerando arquivo de índice", $total );
$stream = $app['twig']->render(
	"Interceptacoes/Views/export/index.html",
	array(
		'rows' => $rows,
		'mode' => $mode,
		'mp3'  => (!empty($opts) && !empty($opts->mp3)) ? 1 : 0
	)
);
file_put_contents("simce.html", $stream);

// Faz a compactação de todo o conteúdo
$handle  = popen( "/usr/bin/zip -dc -r {$zOpts} {$zipFile} *", "r" );
while(!feof($handle)) {
	$str = fgets($handle);
	if (preg_match("/^\s*(\d+)\/(\d+)\s*adding/i", $str, $reg)) {
		$cur   = $reg[1];
		$left  = $reg[2];
		$mtotal = $cur + $left;
		$perc  = sprintf("%d", ($cur*100)/$mtotal);
		updateStatus( "p", "Compactação em $perc%", $total );
	}
}
pclose( $handle );

// Finalizando o arquivo
@chdir(SIMCE_DIR);
exec("/bin/rm -rf {$dir}");
updateStatus( "c", "Exportação finalizada", $total );

/**
 * Atualiza o arquivo de status do processo
 *
 * @param char $status
 * @param string $text
 * @param int $current
 * @return void
 */
function updateStatus( $status, $text, $current ) {

	global $statusFile, $zipFile;

	$size = "-";
	if (file_exists($zipFile)) {
		$base = log(@filesize($zipFile)) / log(1024);
		$suffixes = array('B', 'Kb', 'Mb', 'Gb', 'Tb');   
		$size = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
	}

	file_put_contents( $statusFile, json_encode( array(
		"pid"     => getmypid(),
		"status"  => $status,
		"text"    => $text,
		"current" => $current,
		"size"    => $size,
		"file"    => basename($zipFile)
	)));

}

/**
 * Recebe e trata os sinais recebidos
 *
 * @param int $signo
 * @return void
 */
function sigHandler( $signo ) {
	updateStatus( "e", "Processo cancelado", 0 );
}

?>
