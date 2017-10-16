<?php

/**
 * Classe para fazer o debug das informações
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Debug {
	
	/**
	 * Função para fazer o log de informações de debug
	 * 
	 * @param string $method
	 * @param string $msg
	 * @return void
	 */
	function log( $method, $msg ) {
		
		global $app;

		// Verifica se o debug esta ativo
		if ($app['config']['general']['debug'] == 1) {

			// Obtem o pid
			$pid = getmypid();
			
			// Obtem a data atual
			$date = date('Y-m-d H:i:s');
						
			// Define o nome do arquio de log
			$file = SIMCE_DIR . "/simce.log";
			
			// Verifica o tamanho do arquivo e se for maior que 10MB, faz o rotate
			if (filesize( $file ) >= 10485760 ) {
			
				// Grava o log no arquivo sem append
				file_put_contents( $file, "[$date] [$method] [$pid] $msg\n" );
				
			} else {
				
				// Grava o log no arquivo
				file_put_contents( $file, "[$date] [$method] [$pid] $msg\n", FILE_APPEND );
				
			}
			
		}
		
	}
	
}
