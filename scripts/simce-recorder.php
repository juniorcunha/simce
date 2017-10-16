#!/usr/bin/php	
<?php

/**
 * SiMCE VoiceID
 * 
 * Script que vai armazenar os dados da biometria de voz para
 * o contato selecionado
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
$voiceid_id = $argv[1];

// Carrega o registro
$voiceid = SiMCE\Classes\VoiceID::getByID( $voiceid_id, true );

// Atualiza para processando
$voiceid->set('voicedb',1);
$voiceid->save();

// Obtem a lista de registros afetados pelo voiceid
$records = array();
foreach( $voiceid->bean->ownSegmentos as $seg ) {
	
	// Obtem o registro
	$registro_id = $seg->registros_id;
	$records[] = $registro_id;
	
}

// Remove valores duplicados
sort( $records );
$records = array_unique( $records );

// Verifica os segmentos do voiceid
foreach( $records as $registro_id ) {

	// Define as variáveis que serão utilizadas
	$registro     = SiMCE\Classes\Registros::getByID( $registro_id, true );
	$organization = $registro->get('unidades_id');
	$operation    = $registro->get('operacoes_id');
	$target       = $registro->get('alvos_id');

	// Define a estrutura de diretório e o arquivo json a ser utilizado
	$db_dir      = SIMCE_DIR . "/voiceid/$organization";
	$target_dir  = SIMCE_DIR . "/records/{$organization}/{$operation}/{$target}/" . $registro->bean->id . "_";

	// Faz a leitura e parse do arquivo json
	if (file_exists( $target_dir . ".json" )) {
		
		$lines = file( $target_dir . ".json", FILE_IGNORE_NEW_LINES );
		$json = json_decode( str_replace("'", '"', $lines[0]) );

		// Encontra o speaker no arquivo
		foreach( $json->selections as $spk ) {
			if ($voiceid->get('speaker') == $spk->speaker) {

				// Executa o processo de aprendizagem de voz
				$t1 = microtime(true);
				$app['debug']->log( $serviceName, "Unidade: {$voiceid->bean->unidades->nome}" );
				$app['debug']->log( $serviceName, "Operação: {$voiceid->bean->operacoes->nome}" );
				$app['debug']->log( $serviceName, "Adicionando VoiceID {$voiceid->bean->id} para o contato {$voiceid->bean->contatos->nome}" );
				exec( SIMCE_DIR . "/scripts/voiceid-learn {$db_dir} {$target_dir}/{$spk->speakerLabel}.wav {$spk->speaker} >/dev/null 2>/dev/null" );

				// Atualiza o objeto VoiceID
				$voiceid = SiMCE\Classes\VoiceID::getByID( $voiceid_id, true ); // O objeto tem que ser recriado devido ao overload feito pelo bean
				$voiceid->set('voicedb',2);
				$voiceid->save();

				$t2 = microtime(true) - $t1;
				$app['debug']->log( $serviceName, "Processo finalizado em " . sprintf("%.3f", $t2) . " segundo(s)" );
				break;

			}
		}
	} else {
		
		$app['debug']->log( $serviceName, "Arquivo $target_dir.json não existe mais. Finalizando processamento." );
		
		// Atualiza o objeto VoiceID
		$voiceid = SiMCE\Classes\VoiceID::getByID( $voiceid_id, true ); // O objeto tem que ser recriado devido ao overload feito pelo bean
		$voiceid->set('voicedb',2);
		$voiceid->save();
		
	}
}
		
?>