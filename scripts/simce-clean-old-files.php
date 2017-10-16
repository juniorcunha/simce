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
$bytes = 0;
foreach( $recursiveIterator as $fileInfo ) {
	
	if (substr($fileInfo->getFilename(),0,1) == '.')
		continue;
	
	if ( !preg_match("/\/[\d]+\.(wav|png)$/", $fileInfo->getPathname()) ) {
		// Verifica se é mais antigo que 1 mês e remove
		//if ( (time()- $fileInfo->getMTime()) >= 2592000 ) {
		if ( (time()- $fileInfo->getMTime()) >= 432000 ) {
			//print( $fileInfo->getPathname() . PHP_EOL);
			@unlink( $fileInfo->getPathname() );
			//$bytes += $fileInfo->getSize();
		}
	}

}

?>
