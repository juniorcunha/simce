<?php

/**
 * Classe para gerar uma imagem da rede de relacionamento
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2016
 */

namespace SiMCE\Classes;

class Network {
	
	/**
	 * Obtem o base64 da imagem gerada
	 * 
	 * @param int operacao
	 * @return string
	 */
	static function getGraph( $operacao ) {
		
		// Cria o arquivo que irá conter o gráfico
		$graph_file = SIMCE_CACHE_DIR . "/SIMCE-NEWORK-" . microtime(true) . '.png';
		$cmd = array(
			SIMCE_DIR . "/scripts/phantomjs",
			"--ignore-ssl-errors=yes",
			SIMCE_DIR . "/scripts/rasterize.js",
			"'https://127.0.0.1/simce/network/?full=1&mode=1&operacao=" . $operacao . "'",
			$graph_file,
			//">/dev/null",
			//"2>/dev/null",
		);
		
		// Executa o comando
		system( implode(" ", $cmd) );
		
		// Faz a leitura dos dados gravados
		$stream = base64_encode( file_get_contents( $graph_file ) );
		@unlink( $graph_file );
		
		return $stream;
		
	}
	
}

?>