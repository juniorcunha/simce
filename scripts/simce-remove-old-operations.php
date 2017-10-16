#!/usr/bin/php	
<?php

/**
 * SiMCE Stats
 * 
 * Script responsável por remover arquivos temporários
 * gerados pela biometria de voz
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

// Verifica todos os arquivos do diretório de cache
$iterator = new RecursiveDirectoryIterator( SIMCE_DIR . "/records" );
$recursiveIterator = new RecursiveIteratorIterator($iterator);
$recursiveIterator->setMaxDepth(2);
$bytes = 0;
foreach( $recursiveIterator as $fileInfo ) {

	if (preg_match("/\/(\d+)\/(\d+)$/", $fileInfo->getPath(), $regex)) {

		// Verifica se a operação existe
		$ope = \SiMCE\Classes\Operacoes::getByQuery( 'id = :id', array( 'id' => $regex[2] ) );
		if (empty($ope)) {

			print "Removendo diretório " . $fileInfo->getPath() . PHP_EOL;
			system("/bin/rm -r " . $fileInfo->getPath() . " >/dev/null 2>/dev/null");

		}

	}
	
}

?>
