#!/usr/bin/php
<?php

/**
 * SiMCE Voice Queue
 * 
 * Serviço que irá controlar a fila de registros que precisam
 * ser processados
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../../index.php' );
global $app;

/**
 * Desabilita o timeout
 */
set_time_limit(0);

/**
 * Define o nome do serviço
 */
$serviceName = basename(__FILE__, ".php");

/**
 * Define o número de threads
 */
$numThreads = $app['config']['voiceid']['threads_voice'];

/**
 * Inicializa o serviço
 */
$app['debug']->log( $serviceName, "Inicializando..." );
$pid = pcntl_fork();
if ( $pid == -1 ) { // Erro ao criar o fork
	
	$app['debug']->log( $serviceName, "Erro: Não foi possível iniciar o serviço." );
	return 1;
	
} else if( $pid ){ // Fork criado, encerra o processo pai
	
	$app['debug']->log( $serviceName, "Serviço inicializado com sucesso" );
	return 0;
	
} else { // Fork criado, continua o processamento no filho

	$app['debug']->log( $serviceName, "Ajustando o número de processos simultâneos para $numThreads" );
	while(true) {
	
		// Verifica a fila de processamento
		//$app['debug']->log( $serviceName, "Verificando a fila de processamento" );
		$runningArray = SiMCE\Classes\Registros::getByQuery(
			" attr REGEXP :attr AND tamanho <= :size ",
			array( 
				'attr' => 'voiceid":1',
				'size' => 31457280 // 30 Mbytes
			)
		);
		$runningCount = count($runningArray);
		//$app['debug']->log( $serviceName, "$runningCount processo(s) rodando" );
		
		// Verifica se é possível executar mais
		if ( $runningCount < $numThreads ) {
			
			// Calcula quantos processos podem ser executados
			$toRun = $numThreads - $runningCount;
			//$app['debug']->log( $serviceName, "Executando mais $toRun processo(s)" );
			
			// Obtem os próximos registros
			$rows = SiMCE\Classes\Registros::getByQuery(
				" attr REGEXP :attr AND tamanho <= :size LIMIT :limit ",
				array(
					'attr'  => 'voiceid":0',
					'size' => 31457280, // 30 Mbytes
					'limit' => $toRun
				)
			);
			
			// Faz o loop entre os processos
			foreach( $rows as $row ) {
				
				// Executa a análise pelo ID do registro
				$app['debug']->log( $serviceName, "Analisando ID {$row['id']}" );
				//$app['debug']->log( $serviceName,  SIMCE_DIR . "/scripts/simce-voiceid.php {$row['id']} >/dev/null 2>/dev/null & ");
				exec( SIMCE_DIR . "/scripts/simce-voiceid.php {$row['id']} >/dev/null 2>/dev/null &" );
								
			}
			
		} else { // Limite atingido
			//$app['debug']->log( $serviceName, "Limite de processos atingido. Aguardando..." );
		}
		
		// Verifica se os processos estão realmente rodando
		foreach( $runningArray as $row ) {

			exec( "/usr/bin/pgrep -f '/scripts/simce-voiceid.php {$row["id"]}'", $output, $ret );
			if ($ret) { // Não está sendo executado

				$app['debug']->log( $serviceName, "Registro {$row['id']} não está sendo processado. Removendo thread." );
				
				// Atualiza o registro para processado
				$registro = SiMCE\Classes\Registros::getByID( $row['id'], true );
				$obj = $registro->get('attr');
				$obj->voiceid = 2;
				$registro->set('attr', json_encode($obj) );
				$registro->save();
				
			}

		}
		
		// Aguarda 10 segundos para não sobrecarregar o sistema
		sleep(10);
		
	}
}

?>
