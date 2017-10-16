#!/usr/bin/php	
<?php

/**
 * SiMCE VoiceID
 * 
 * Script que vai processar a biometria de voz
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

global $app;

// Define o nome do serviço
$serviceName = basename(__FILE__, ".php");

// Obtem o argumento via argv
$registro_id = $argv[1];

// Carrega o registro
$registro     = SiMCE\Classes\Registros::getByID( $registro_id, true );
$organization = $registro->get('unidades_id');
$operation    = $registro->get('operacoes_id');
$target       = $registro->get('alvos_id');

// Cria a estrutura de diretório para o banco da unidade
$db_dir = SIMCE_DIR . "/voiceid/$organization";
system("/bin/mkdir -p $db_dir");

// Define a estrutura de diretório do arquivo de áudio
$target_dir  = __DIR__ . "/../records/{$organization}/{$operation}/{$target}";
$target_file = "$target_dir/" . $registro->bean->id . ".wav";

// Obtem o tamanho do arquivo
$size = filesize( $target_file );
if ( $size < 1048576 ) // 1MB
	$size = sprintf("%d KB", $size/1024);
else
	$size = sprintf("%.1f MB", $size/1048576);

// Marca o registro como início de processamento
$obj = $registro->get('attr');
$obj->voiceid = 1;
$registro->set('attr', json_encode($obj) );
$registro->save();

// Executa a diarização do áudio
$t1 = microtime(true);
$app['debug']->log( $serviceName, "Processando arquivo $target_file ($size)..." );
exec( __DIR__ . "/voiceid $db_dir $target_file", $output );

// Verifica o retorno
foreach( $output as $ret ) {

	// Faz o parse do resultado
	$values = explode("|", $ret);

	// Ignora caso a linha seja um warning
	if (empty($values[1]))
		continue;

	// Obtem o speaker e o wav gerado
	$speaker  = trim($values[0]);
	$wav_file = trim($values[1]);

	// Verifica se o speaker do VoiceID ja existe na base
	$voiceid = \SiMCE\Classes\VoiceID::getByQuery(
		" speaker = :speaker AND unidades_id  = :unidade ",
		array(
			"speaker" => $speaker,
			"unidade" => $organization
		),
		true
	);
	if (count($voiceid)) { // VoiceID encontrado
		$voiceid = $voiceid[0];
		$app['debug']->log( $serviceName, "Encontrado VoiceID ID " . $voiceid->bean->id . " - " . $voiceid->bean->speaker );
	} else { // Se não existe, adiciona um novo VoiceID na base

		$app['debug']->log( $serviceName, "Criando novo VoiceID $speaker" );
		$voiceid = new \SiMCE\Classes\VoiceID();
		$voiceid->set('unidades', \SiMCE\Classes\Unidades::getByID($organization,true)->bean);
		$voiceid->set('operacoes', \SiMCE\Classes\Operacoes::getByID($operation,true)->bean);
		$voiceid->set('speaker',$speaker);
		$voiceid->set('voicedb',null);
		$voiceid->save();

	}

	// Armazena os segmentos de audio para o VoiceID
	for( $i = 2; $i<count($values); $i++ ) {

		list ( $spk_start, $spk_end ) = explode("-", trim($values[$i]));
		$spk_start = preg_replace("/\,[\d]{3}/", "", $spk_start);
		$spk_end = preg_replace("/\,[\d]{3}/", "", $spk_end);

		// Cria o segmento de audio e armazena no banco
		$app['debug']->log( $serviceName, "Adicionando segmento $spk_start - $spk_end" );
		$seg = new \SiMCE\Classes\Segmentos();
		$seg->set('registros', $registro->bean);
		$seg->set('voiceid', $voiceid->bean);
		$seg->set('inicio', $spk_start);
		$seg->set('final', $spk_end);
		$seg->set('transcricao', null);
		$seg->save();

	}

}

// Informa que finalizou de processar o reconhecimento de voz
$obj = $registro->get('attr');
$obj->voiceid = 2;
$registro->set('attr', json_encode($obj) );
$registro->save();

// Finaliza a execução
$t2 = microtime(true) - $t1;
$app['debug']->log( $serviceName, "Processo finalizado em " . sprintf("%.3f", $t2) . " segundo(s)" );

?>