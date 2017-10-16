#!/usr/bin/php	
<?php

/**
 * SiMCE Copy PCAP
 * 
 * Script responsável por mover os arquivos PCAP para o Xplico processar
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

// Define o nome do serviço
$serviceName = basename(__FILE__, ".php");

// Verifica todos os arquivos do diretório de cache
$iterator = new RecursiveDirectoryIterator( SIMCE_DIR . "/pcap" );
$recursiveIterator = new RecursiveIteratorIterator($iterator);
$bytes = 0;
foreach( $recursiveIterator as $fileInfo ) {
	
	if (substr($fileInfo->getFilename(),0,1) == '.')
		continue;
	
	if ( preg_match("/\/SD(\d+)\/.*\.cap$/", $fileInfo->getPathname(), $reg) ) {
		
		$xplico_id  = intval( $reg[1] );
		$new_target = "/opt/xplico/pol_{$xplico_id}/sol_{$xplico_id}/new/" . str_replace( ".cap", ".pcap", $fileInfo->getFilename() );
		
		$app['debug']->log( $serviceName, "Movendo arquivo " . $fileInfo->getPathname() . " para " . $new_target );
		rename( $fileInfo->getPathname(), $new_target );
		

	}

}

?>